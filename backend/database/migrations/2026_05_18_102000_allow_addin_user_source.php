<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE users SET source = 'manual' WHERE source IS NULL OR source NOT IN ('manual', 'entra', 'ad_sync', 'addin')");
        DB::statement('DROP TABLE IF EXISTS users_new');

        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys=off');
        DB::statement('CREATE TABLE users_new (
            id integer primary key autoincrement not null,
            entra_id varchar null,
            name varchar not null,
            email varchar not null,
            username varchar null,
            domain varchar null,
            department_id integer null,
            title varchar null,
            company varchar null,
            phone varchar null,
            mobile varchar null,
            office varchar null,
            address varchar null,
            website varchar null,
            is_active tinyint(1) not null default 1,
            source varchar check (source in ("manual", "entra", "ad_sync", "addin")) not null default "manual",
            last_seen_at datetime null,
            email_verified_at datetime null,
            password varchar null,
            remember_token varchar null,
            created_at datetime null,
            updated_at datetime null,
            foreign key(department_id) references departments(id) on delete set null
        )');
        DB::statement('INSERT INTO users_new (
            id, entra_id, name, email, username, domain, department_id, title, company, phone, mobile, office,
            address, website, is_active, source, last_seen_at, email_verified_at, password, remember_token,
            created_at, updated_at
        )
        SELECT
            id, entra_id, name, email, username, domain, department_id, title, company, phone, mobile, office,
            address, website, is_active, COALESCE(source, "manual"), last_seen_at, email_verified_at, password,
            remember_token, created_at, updated_at
        FROM users');
        DB::statement('DROP TABLE users');
        DB::statement('ALTER TABLE users_new RENAME TO users');
        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        DB::statement('CREATE UNIQUE INDEX users_entra_id_unique ON users (entra_id)');
        DB::statement('PRAGMA foreign_keys=on');
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET source = 'manual' WHERE source IS NULL OR source = 'addin'");
        DB::statement('DROP TABLE IF EXISTS users_new');

        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys=off');
        DB::statement('CREATE TABLE users_new (
            id integer primary key autoincrement not null,
            entra_id varchar null,
            name varchar not null,
            email varchar not null,
            username varchar null,
            domain varchar null,
            department_id integer null,
            title varchar null,
            company varchar null,
            phone varchar null,
            mobile varchar null,
            office varchar null,
            address varchar null,
            website varchar null,
            is_active tinyint(1) not null default 1,
            source varchar check (source in ("manual", "entra", "ad_sync")) not null default "manual",
            last_seen_at datetime null,
            email_verified_at datetime null,
            password varchar null,
            remember_token varchar null,
            created_at datetime null,
            updated_at datetime null,
            foreign key(department_id) references departments(id) on delete set null
        )');
        DB::statement('INSERT INTO users_new (
            id, entra_id, name, email, username, domain, department_id, title, company, phone, mobile, office,
            address, website, is_active, source, last_seen_at, email_verified_at, password, remember_token,
            created_at, updated_at
        )
        SELECT
            id, entra_id, name, email, username, domain, department_id, title, company, phone, mobile, office,
            address, website, is_active, COALESCE(source, "manual"), last_seen_at, email_verified_at, password,
            remember_token, created_at, updated_at
        FROM users');
        DB::statement('DROP TABLE users');
        DB::statement('ALTER TABLE users_new RENAME TO users');
        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        DB::statement('CREATE UNIQUE INDEX users_entra_id_unique ON users (entra_id)');
        DB::statement('PRAGMA foreign_keys=on');
    }
};
