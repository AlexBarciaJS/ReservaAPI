<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615034138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP CONSTRAINT fk_42c849559b4d58ce
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP CONSTRAINT fk_42c849552e3c6f2d
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_42c849552e3c6f2d
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_42c849559b4d58ce
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD space_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP user_relation_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP space_relation_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C8495523575340 FOREIGN KEY (space_id) REFERENCES space (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C84955A76ED395 ON reservation (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C8495523575340 ON reservation (space_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP CONSTRAINT FK_42C84955A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP CONSTRAINT FK_42C8495523575340
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_42C84955A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_42C8495523575340
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD user_relation_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD space_relation_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP space_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT fk_42c849559b4d58ce FOREIGN KEY (user_relation_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT fk_42c849552e3c6f2d FOREIGN KEY (space_relation_id) REFERENCES space (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_42c849552e3c6f2d ON reservation (space_relation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_42c849559b4d58ce ON reservation (user_relation_id)
        SQL);
    }
}
