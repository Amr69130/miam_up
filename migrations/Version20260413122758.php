<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413122758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE amitie (id INT AUTO_INCREMENT NOT NULL, utilisateur_demandeur_id INT NOT NULL, utilisateur_receveur_id INT NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_8FF9F39CFC54CAAA (utilisateur_demandeur_id), INDEX IDX_8FF9F39C4D81EBB5 (utilisateur_receveur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE amitie ADD CONSTRAINT FK_8FF9F39CFC54CAAA FOREIGN KEY (utilisateur_demandeur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE amitie ADD CONSTRAINT FK_8FF9F39C4D81EBB5 FOREIGN KEY (utilisateur_receveur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE amitie DROP FOREIGN KEY FK_8FF9F39CFC54CAAA');
        $this->addSql('ALTER TABLE amitie DROP FOREIGN KEY FK_8FF9F39C4D81EBB5');
        $this->addSql('DROP TABLE amitie');
    }
}
