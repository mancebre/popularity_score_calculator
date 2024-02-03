<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240202122212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create search_result table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('search_result');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('term', 'string', ['length' => 255]);
        $table->addColumn('score', 'float');
        $table->addColumn('created_at', 'datetime');
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('search_result');
    }
}
