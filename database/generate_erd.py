#!/usr/bin/env python3
"""
Generates an ERD (as PNG) from the live database's actual information_schema
metadata — not from the SQL dump/migration files — so the diagram always
reflects exactly what's really running. Requires the `mysql` CLI and
Graphviz's `dot` to be installed and on PATH.

Reads DB connection info from .env in the project root (same vars the app
itself uses — see src/Core/Database.php).

Usage:
  python3 database/generate_erd.py [output.png]     # one big ERD, all tables
  python3 database/generate_erd.py --split [outdir]  # one PNG per module
                                                       # (grouping from schema_modules.py)
"""
import os
import subprocess
import sys
from collections import defaultdict

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def load_env():
    env = {}
    env_path = os.path.join(ROOT, '.env')
    with open(env_path) as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith('#') or '=' not in line:
                continue
            key, _, value = line.partition('=')
            env[key.strip()] = value.strip().strip('"').strip("'")
    return env


def mysql_query(env, sql):
    cmd = [
        'mysql',
        '--protocol=TCP',
        '-h', env.get('DB_HOST', '127.0.0.1'),
        '-P', str(env.get('DB_PORT', '3306')),
        '-u', env.get('DB_USERNAME', 'root'),
        f"-p{env.get('DB_PASSWORD', '')}",
        '-N', '-B',
        env.get('DB_DATABASE', 'mental_health'),
        '-e', sql,
    ]
    result = subprocess.run(cmd, capture_output=True, text=True)
    if result.returncode != 0:
        stderr = result.stderr.replace(f"-p{env.get('DB_PASSWORD', '')}", '-p***')
        raise RuntimeError(f"mysql query failed: {stderr}")
    rows = [line.split('\t') for line in result.stdout.splitlines() if line]
    return rows


def fetch_schema(env):
    db = env.get('DB_DATABASE', 'mental_health')

    tables = [r[0] for r in mysql_query(
        env,
        f"SELECT table_name FROM information_schema.tables "
        f"WHERE table_schema = '{db}' AND table_type = 'BASE TABLE' "
        f"ORDER BY table_name"
    )]

    columns = defaultdict(list)
    for table, col, coltype, nullable, colkey, extra in mysql_query(
        env,
        f"SELECT table_name, column_name, column_type, is_nullable, column_key, extra "
        f"FROM information_schema.columns WHERE table_schema = '{db}' "
        f"ORDER BY table_name, ordinal_position"
    ):
        columns[table].append({
            'name': col,
            'type': coltype,
            'nullable': nullable == 'YES',
            'key': colkey,
            'extra': extra,
        })

    fks = []
    for table, col, ref_table, ref_col, constraint in mysql_query(
        env,
        f"SELECT table_name, column_name, referenced_table_name, referenced_column_name, constraint_name "
        f"FROM information_schema.key_column_usage "
        f"WHERE table_schema = '{db}' AND referenced_table_name IS NOT NULL "
        f"ORDER BY table_name, ordinal_position"
    ):
        fks.append({
            'table': table,
            'column': col,
            'ref_table': ref_table,
            'ref_column': ref_col,
            'name': constraint,
        })

    return tables, columns, fks


def escape(s):
    return s.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')


def build_dot(tables, columns, fks, stub_tables=None):
    """
    tables: full tables to render with every column.
    stub_tables: tables rendered as a small title-only box (no columns) — used
    when splitting the ERD per module, so a module's diagram still shows *that*
    it relates to an outside table (e.g. users) without repeating that outside
    table's entire column list in every module's diagram.
    """
    stub_tables = stub_tables or set()
    all_nodes = set(tables) | set(stub_tables)

    fk_columns = defaultdict(set)
    for fk in fks:
        fk_columns[fk['table']].add(fk['column'])

    lines = []
    lines.append('digraph ERD {')
    lines.append('  rankdir=TB;')
    lines.append('  concentrate=true;')
    lines.append('  graph [fontname="Helvetica", nodesep=0.5, ranksep=1.3, splines=polyline, bgcolor="white"];')
    lines.append('  node [shape=plaintext, fontname="Helvetica"];')
    lines.append('  edge [fontname="Helvetica", fontsize=9, fontcolor="#475569", color="#94a3b8", arrowhead=crow, arrowtail=none, dir=back];')
    lines.append('')

    for table in tables:
        rows = []
        rows.append(
            f'<tr><td bgcolor="#1e293b" align="center">'
            f'<font color="white" point-size="13"><b>{escape(table)}</b></font></td></tr>'
        )
        for col in columns[table]:
            name = escape(col['name'])
            coltype = escape(col['type'])
            is_pk = col['key'] == 'PRI'
            is_fk = col['name'] in fk_columns[table]

            label = name
            if is_pk:
                label = f'<b>🔑 {name}</b>'
            elif is_fk:
                label = f'<i>{name}</i>'

            suffix = ''
            if not col['nullable'] and not is_pk:
                suffix = ' NOT NULL'

            port = f' port="{col["name"]}"'
            align = 'align="left"'
            rows.append(
                f'<tr><td {align}{port}>{label} '
                f'<font color="#64748b" point-size="10">{coltype}{suffix}</font></td></tr>'
            )

        table_html = (
            f'  "{table}" [label=<<table border="0" cellborder="1" cellspacing="0" cellpadding="5">'
            + ''.join(rows) + '</table>>];'
        )
        lines.append(table_html)

    for table in stub_tables:
        lines.append(
            f'  "{table}" [label=<<table border="0" cellborder="1" cellspacing="0" cellpadding="6">'
            f'<tr><td bgcolor="#cbd5e1"><font color="#1e293b" point-size="12">{escape(table)}</font></td></tr>'
            f'</table>>];'
        )

    lines.append('')

    for fk in fks:
        if fk['table'] not in all_nodes or fk['ref_table'] not in all_nodes:
            continue
        src = f'"{fk["table"]}"' if fk['table'] in stub_tables else f'"{fk["table"]}":"{fk["column"]}"'
        dst = f'"{fk["ref_table"]}"' if fk['ref_table'] in stub_tables else f'"{fk["ref_table"]}":"{fk["ref_column"]}"'
        label = escape(fk['column'])
        lines.append(f'  {dst} -> {src} [label="{label}"];')

    lines.append('}')
    return '\n'.join(lines)


# Tabel yang mungkin belum ada di DB live (baru ditambahkan lewat migrasi
# terbaru) tapi tetap mau ditampilkan di ERD. Kolomnya harus sinkron dengan
# MANUAL_TABLES di generate_db_doc.py.
MANUAL_TABLES = {
    'booking_cancellation_requests': {
        'columns': [
            {'name': 'id', 'type': 'int unsigned', 'nullable': False, 'key': 'PRI', 'extra': 'auto_increment'},
            {'name': 'booking_id', 'type': 'int', 'nullable': False, 'key': 'MUL', 'extra': ''},
            {'name': 'previous_status', 'type': "enum('Pending','Confirmed')", 'nullable': False, 'key': '', 'extra': ''},
            {'name': 'reason', 'type': 'text', 'nullable': True, 'key': '', 'extra': ''},
            {'name': 'status', 'type': "enum('Pending','Approved','Rejected')", 'nullable': False, 'key': '', 'extra': ''},
            {'name': 'admin_notes', 'type': 'text', 'nullable': True, 'key': '', 'extra': ''},
            {'name': 'reviewed_by', 'type': 'int', 'nullable': True, 'key': 'MUL', 'extra': ''},
            {'name': 'reviewed_at', 'type': 'datetime', 'nullable': True, 'key': '', 'extra': ''},
            {'name': 'created_at', 'type': 'datetime', 'nullable': False, 'key': '', 'extra': 'DEFAULT_GENERATED'},
        ],
        'fks': [
            {'column': 'booking_id', 'ref_table': 'counseling_bookings', 'ref_column': 'booking_id'},
            {'column': 'reviewed_by', 'ref_table': 'users', 'ref_column': 'id'},
        ],
    },
}


def merge_manual_tables(tables, columns, fks):
    tables_set = set(tables)
    for name, info in MANUAL_TABLES.items():
        if name in tables_set:
            continue
        tables.append(name)
        columns[name] = info['columns']
        for fk in info['fks']:
            fks.append({'table': name, 'column': fk['column'], 'ref_table': fk['ref_table'], 'ref_column': fk['ref_column'], 'name': ''})
        print(f'  + {name} ditambahkan manual (belum ada di DB live).', file=sys.stderr)
    return tables, columns, fks


def render(dot_source, dot_path, out_png):
    with open(dot_path, 'w') as f:
        f.write(dot_source)
    subprocess.run(['dot', '-Tpng', dot_path, '-o', out_png], check=True)
    print(f'Wrote {out_png}', file=sys.stderr)


def generate_full(tables, columns, fks):
    out_png = os.path.join(ROOT, 'database', 'erd.png')
    dot_path = os.path.join(ROOT, 'database', 'erd.dot')
    dot_source = build_dot(tables, columns, fks)
    render(dot_source, dot_path, out_png)


def generate_split(tables, columns, fks, out_dir):
    from schema_modules import MODULES

    os.makedirs(out_dir, exist_ok=True)

    # tabel -> nomor modul, untuk memutuskan mana yang jadi stub di modul lain
    module_of = {}
    for i, (_, table_list) in enumerate(MODULES):
        for t in table_list:
            module_of[t] = i

    for i, (module_name, table_list) in enumerate(MODULES):
        focus = [t for t in table_list if t in columns]
        if not focus:
            continue

        focus_set = set(focus)
        stub_set = set()
        for fk in fks:
            if fk['table'] in focus_set and fk['ref_table'] not in focus_set and fk['ref_table'] in columns:
                stub_set.add(fk['ref_table'])
            if fk['ref_table'] in focus_set and fk['table'] not in focus_set and fk['table'] in columns:
                stub_set.add(fk['table'])

        dot_source = build_dot(focus, columns, fks, stub_tables=stub_set)

        slug = module_name.split('.', 1)[0].strip()
        name_part = module_name.split('.', 1)[1].strip().lower()
        for a, b in [(' ', '_'), ('&', 'dan'), ('(', ''), (')', ''), (',', ''), ('/', '_'), ('__', '_')]:
            name_part = name_part.replace(a, b)
        filename = f"{int(slug):02d}_{name_part}"

        dot_path = os.path.join(out_dir, f'{filename}.dot')
        out_png = os.path.join(out_dir, f'{filename}.png')
        render(dot_source, dot_path, out_png)


def main():
    args = sys.argv[1:]
    env = load_env()

    print('Fetching schema from live database...', file=sys.stderr)
    tables, columns, fks = fetch_schema(env)
    print(f'Found {len(tables)} tables, {len(fks)} foreign keys.', file=sys.stderr)
    tables, columns, fks = merge_manual_tables(tables, columns, fks)

    if args and args[0] == '--split':
        out_dir = args[1] if len(args) > 1 else os.path.join(ROOT, 'database', 'erd')
        generate_split(tables, columns, fks, out_dir)
    else:
        generate_full(tables, columns, fks)


if __name__ == '__main__':
    main()
