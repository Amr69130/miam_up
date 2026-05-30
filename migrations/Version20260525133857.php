<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260525133857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration conservée pour compatibilité, sans modification de schéma';
    }

    public function up(Schema $schema): void
    {
        // No-op: la table utilisateur_favoris est déjà créée par la migration précédente.
    }

    public function down(Schema $schema): void
    {
        // No-op: aucun changement de schéma à annuler.
    }
}
