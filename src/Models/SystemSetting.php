<?php

namespace App\Models;

class SystemSetting
{
    public int $id;
    public string $settingKey;
    public string $settingValue;

    public function __construct(array $data)
    {
        $this->id            = (int) ($data['id'] ?? 0);
        $this->settingKey    = $data['setting_key'] ?? '';
        $this->settingValue  = $data['setting_value'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'setting_key'    => $this->settingKey,
            'setting_value'  => $this->settingValue,
        ];
    }
}
