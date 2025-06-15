<?php

namespace WindBox\Infrastructure\Persistence\Migrations;

use PDO;

interface Migration
{
    public function up(PDO $pdo): void;
    public function down(PDO $pdo): void;
}