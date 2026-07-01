<?php
declare(strict_types=1);
 
namespace App\Models;
 
class User
{
    public ?int $id = null;
    public string $email;
    public string $password_hash;
    public string $first_name;
    public string $last_name;
    public ?string $bio = null;
    public ?string $profile_photo = null;
    public ?string $faculty = null;
    public ?string $year = null;
    public bool $is_active = true;
    public ?string $password_reset_token = null;
    public ?string $password_reset_expires = null;
    public string $created_at;
    public string $updated_at;
 
    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                // Cast is_active from MySQL int (0/1) to bool
                if ($k === 'is_active') {
                    $this->{$k} = (bool) $v;
                } else {
                    $this->{$k} = $v;
                }
            }
        }
    }
}