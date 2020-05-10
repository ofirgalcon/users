<?php
/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Users_model::class, function (Faker\Generator $faker) {
    $name = $faker->name();
    $userName = str_replace(' ', '.', str_replace('.', '', trim(strtolower($name))));
    return [
        'real_name' => $name,
        'ard_priv' => $faker->word(),
        'node_location' => $faker->word(),
        'password_hint' => $faker->word(),
        'generated_uuid' => $faker->uuid(),
        'home_directory' => '/Users/' . $userName,
        'primary_group_id' => $faker->randomElement([20, 80]),
        'record_name' => $userName,
        'group_memership' => $faker->word(),
        'administrator' => $faker->numberBetween(0, 1),
        'ssh_access' => $faker->numberBetween(0, 1),
        'screenshare_access' => $faker->numberBetween(0, 1),
        'unique_id' => 501,
        'user_shell' => $faker->randomElement(['/bin/bash', '/bin/zsh', '/bin/csh']),
        'meta_record_name' => $faker->word(),
        'email_address' => $faker->safeEmail(),
        'smb_group_rid' => $faker->randomNumber(),
        'smb_home' => $faker->word(),
        'smb_home_drive' => $faker->word(),
        'smb_primary_group_sid' => $faker->word(),
        'smb_sid' => $faker->randomNumber(),
        'smb_script_path' => $faker->word(),
        'original_node_name' => $faker->word(),
        'primary_nt_domain' => $faker->word(),
        'copy_timestamp' => $faker->dateTimeBetween('-4 years')->format('U'),
        'smb_password_last_set' => $faker->dateTimeBetween('-4 years')->format('U'),
        'creation_time' => $faker->dateTimeBetween('-4 years')->format('U'),
        'failed_login_count' => $faker->randomNumber(),
        'failed_login_timestamp' => $faker->dateTimeBetween('-4 years')->format('U'),
        'password_last_set_time' => $faker->dateTimeBetween('-4 years')->format('U'),
        'last_login_timestamp' => $faker->dateTimeBetween('-4 years')->format('U'),
        'password_history_depth' => $faker->randomNumber(),
        'linked_full_name' => $faker->word(),
        'linked_timestamp' => $faker->dateTimeBetween('-4 years')->format('U'),
    ];
});

