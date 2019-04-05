<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190115194228 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE movie_list (id INT AUTO_INCREMENT NOT NULL, list_id_id INT NOT NULL, api_id INT NOT NULL, title VARCHAR(255) NOT NULL, overview LONGTEXT NOT NULL, note INT DEFAULT NULL, year DATE DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_B7AED915A6D70A54 (list_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE movie_list ADD CONSTRAINT FK_B7AED915A6D70A54 FOREIGN KEY (list_id_id) REFERENCES liste (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE movie_list');
    }
}
