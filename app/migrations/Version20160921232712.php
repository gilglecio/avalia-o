<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160921232712 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS `alternatives` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `answers` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `evaluation_sending_id` int(10) unsigned NOT NULL,
                `issue_id` int(10) unsigned NOT NULL,
                `valued_id` int(10) unsigned NOT NULL,
                `answer` text COLLATE utf8_unicode_ci,
                `justification` text COLLATE utf8_unicode_ci,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `status` smallint(6) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `fk_answers_issues1` (`issue_id`),
                KEY `fk_answers_users1` (`valued_id`),
                KEY `fk_answers_evaluation_sendings1` (`evaluation_sending_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `averages` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `average` float NOT NULL,
                `evaluator_id` int(10) unsigned NOT NULL,
                `valued_id` int(10) unsigned NOT NULL,
                `created_at` datetime NOT NULL,
                `status` smallint(6) NOT NULL DEFAULT \'0\',
                `updated_at` datetime DEFAULT NULL,
                `evaluation_sending_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id`),
                KEY `fk_averages_users1` (`valued_id`),
                KEY `fk_averages_users2` (`evaluator_id`),
                KEY `fk_averages_evaluation_sendings1` (`evaluation_sending_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `charges` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `corrections` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `answer_id` int(10) unsigned NOT NULL,
                `evaluator_id` int(10) unsigned NOT NULL,
                `note` float NOT NULL,
                `justification` text COLLATE utf8_unicode_ci,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `fk_corrections_answers1` (`answer_id`),
                KEY `fk_corrections_users1` (`evaluator_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `evaluations` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(10) unsigned NOT NULL,
                `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                `subject` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                `start_at` datetime NOT NULL,
                `end_at` datetime NOT NULL,
                `message_email` text COLLATE utf8_unicode_ci,
                `mail_bcc` text COLLATE utf8_unicode_ci,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `status` smallint(6) NOT NULL DEFAULT \'0\',
                `is_alerted` tinyint(1) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `fk_evaluations_users1` (`user_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `evaluation_evaluators` (
                `evaluation_id` int(10) unsigned NOT NULL,
                `evaluator_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`evaluation_id`,`evaluator_id`),
                KEY `fk_evaluations_has_users_users1` (`evaluator_id`),
                KEY `fk_evaluations_has_users_evaluations1` (`evaluation_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `evaluation_groups` (
                `evaluation_id` int(10) unsigned NOT NULL,
                `group_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`evaluation_id`,`group_id`),
                KEY `fk_evaluations_has_groups_groups1` (`group_id`),
                KEY `fk_evaluations_has_groups_evaluations1` (`evaluation_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `evaluation_questionnaires` (
                `evaluation_id` int(10) unsigned NOT NULL,
                `questionnaire_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`evaluation_id`,`questionnaire_id`),
                KEY `fk_evaluations_has_questionnaires_questionnaires1` (`questionnaire_id`),
                KEY `fk_evaluations_has_questionnaires_evaluations1` (`evaluation_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `evaluation_sendings` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `evaluation_id` int(10) unsigned NOT NULL,
                `created_at` datetime NOT NULL,
                `name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `fk_sendings_evaluations1` (`evaluation_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `groups` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `is_delete` tinyint(1) NOT NULL DEFAULT '0',
                `status` smallint(6) NOT NULL DEFAULT '1',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `group_members` (
                `group_id` int(10) unsigned NOT NULL,
                `user_id` int(10) unsigned NOT NULL,
                `status` smallint(6) NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`group_id`,`user_id`),
                KEY `fk_groups_has_users_users1` (`user_id`),
                KEY `fk_groups_has_users_groups` (`group_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `issues` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(10) unsigned NOT NULL,
                `enunciation` text COLLATE utf8_unicode_ci NOT NULL,
                `type` varchar(25) COLLATE utf8_unicode_ci NOT NULL COMMENT 'open;\nscale;\nalternative;\nalternatives;\nboolean;\n0-10;',
                `status` smallint(6) NOT NULL DEFAULT '1',
                `is_delete` tinyint(1) NOT NULL DEFAULT '0',
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `required` tinyint(1) NOT NULL DEFAULT '1',
                `accepted_justification` tinyint(1) NOT NULL DEFAULT '0',
                `justification_required` tinyint(1) NOT NULL DEFAULT '0',
                `page` smallint(6) NOT NULL DEFAULT '1',
                `min_choice` smallint(6) DEFAULT '1',
                `max_choice` smallint(6) DEFAULT NULL,
                `max_note` smallint(6) NOT NULL DEFAULT '10',
                `min_note` smallint(6) NOT NULL DEFAULT '0',
                `scale_id` int(10) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `fk_issues_users1` (`user_id`),
                KEY `fk_issues_scales1` (`scale_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `issue_alternatives` (
                `issue_id` int(10) unsigned NOT NULL,
                `alternative_id` int(10) unsigned NOT NULL,
                `position` smallint(6) NOT NULL DEFAULT '0',
                PRIMARY KEY (`issue_id`,`alternative_id`),
                KEY `fk_issues_has_alternatives_alternatives1` (`alternative_id`),
                KEY `fk_issues_has_alternatives_issues1` (`issue_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `models` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `pdfs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `sending_id` int(11) NOT NULL,
                `evaluator_id` int(11) NOT NULL,
                `salary` float(10,2) NOT NULL,
                `new_salary` float(10,2) DEFAULT NULL,
                `final_note` float(10,2) DEFAULT NULL,
                `perf` float(10,2) NOT NULL,
                `bonus` float(10,2) NOT NULL,
                `bonus_prop` float(10,2) NOT NULL,
                `nr_salary_prop` float(10,2) NOT NULL,
                `perf_prop` float(10,2) NOT NULL,
                `nr_salary` float(10,2) NOT NULL,
                `comment` text COLLATE utf8_unicode_ci NOT NULL,
                `evaluator_note` float(10,2) NOT NULL,
                `is_available` tinyint(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `sending_id` (`sending_id`),
                KEY `evaluator_id` (`evaluator_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `questionnaires` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `name_private` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `is_delete` tinyint(1) NOT NULL DEFAULT '0',
                `user_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id`),
                KEY `fk_questionnaires_users1` (`user_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `questionnaire_issues` (
                `questionnaire_id` int(10) unsigned NOT NULL,
                `issue_id` int(10) unsigned NOT NULL,
                `order` int(11) NOT NULL DEFAULT '10',
                `value` float NOT NULL DEFAULT '10',
                PRIMARY KEY (`questionnaire_id`,`issue_id`),
                KEY `fk_questionnaires_has_issues_issues1` (`issue_id`),
                KEY `fk_questionnaires_has_issues_questionnaires1` (`questionnaire_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ratings` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `scales` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `scale_options` (
                `scale_id` int(10) unsigned NOT NULL,
                `scale_row_id` int(10) unsigned NOT NULL,
                `position` smallint(6) NOT NULL DEFAULT '1',
                PRIMARY KEY (`scale_id`,`scale_row_id`),
                KEY `fk_scale_names_has_scale_rows_scale_rows1` (`scale_row_id`),
                KEY `fk_scale_names_has_scale_rows_scale_names1` (`scale_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `scale_rows` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                `position` smallint(6) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `sendings` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `token` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL,
                `valued_id` int(10) unsigned NOT NULL,
                `status` smallint(6) NOT NULL DEFAULT '0',
                `evaluation_sending_id` int(10) unsigned NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `answered_at` datetime DEFAULT NULL,
                `corrected_at` datetime DEFAULT NULL,
                `viewed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `fk_sendings_users1` (`valued_id`),
                KEY `fk_sendings_evaluation_sendings1` (`evaluation_sending_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `sending_bcc` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `evaluation_sending_id` int(11) NOT NULL,
                `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                `email` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                `token` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `sending_evaluators` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `evaluator_id` int(10) unsigned NOT NULL,
                `evaluation_sending_id` int(10) unsigned NOT NULL,
                `sending_id` int(10) unsigned NOT NULL,
                `token` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL,
                `evaluate` tinyint(1) NOT NULL DEFAULT '1',
                `is_corrected` tinyint(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `fk_sending_evaluators_users1` (`evaluator_id`),
                KEY `fk_sending_evaluators_evaluation_sendings1` (`evaluation_sending_id`),
                KEY `fk_sending_evaluators_sendings1` (`sending_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `site_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                `site_description` text COLLATE utf8_unicode_ci NOT NULL,
                `src_logo` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
                `status` smallint(6) NOT NULL,
                `site_email` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                `email` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                `username` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
                `password` char(60) COLLATE utf8_unicode_ci DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `is_delete` tinyint(1) NOT NULL DEFAULT '0',
                `status` smallint(6) NOT NULL DEFAULT '0' COMMENT '0=n confirmado;\n1=confirmado;\n2=bloqueado;',
                `birth` date DEFAULT NULL,
                `graduated_at` smallint(4) DEFAULT NULL,
                `salary` float(10,2) DEFAULT NULL,
                `profile_type` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1' COMMENT '1=avaliado;\n2=avaliador;',
                `entry_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `user_charges` (
                `user_id` int(10) unsigned NOT NULL,
                `charge_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`user_id`,`charge_id`),
                KEY `fk_users_has_charges_charges1` (`charge_id`),
                KEY `fk_users_has_charges_users1` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS `user_ratings` (
                `user_id` int(10) unsigned NOT NULL,
                `rating_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`user_id`,`rating_id`),
                KEY `fk_users_has_ratings_ratings1` (`rating_id`),
                KEY `fk_users_has_ratings_users1` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->addSql("
            ALTER TABLE `answers`
              ADD CONSTRAINT `fk_answers_evaluation_sendings1` 
              FOREIGN KEY (`evaluation_sending_id`) 
              REFERENCES `evaluation_sendings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_answers_issues1` 
              FOREIGN KEY (`issue_id`) 
              REFERENCES `issues` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_answers_users1` 
              FOREIGN KEY (`valued_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `averages`
              ADD CONSTRAINT `fk_averages_evaluation_sendings1` 
              FOREIGN KEY (`evaluation_sending_id`) 
              REFERENCES `evaluation_sendings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_averages_users1` 
              FOREIGN KEY (`valued_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_averages_users2` 
              FOREIGN KEY (`evaluator_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `corrections`
              ADD CONSTRAINT `fk_corrections_answers1` 
              FOREIGN KEY (`answer_id`) 
              REFERENCES `answers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_corrections_users1` 
              FOREIGN KEY (`evaluator_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `evaluations`
              ADD CONSTRAINT `fk_evaluations_users1` 
              FOREIGN KEY (`user_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `evaluation_evaluators`
              ADD CONSTRAINT `fk_evaluations_has_users_evaluations1` 
              FOREIGN KEY (`evaluation_id`) 
              REFERENCES `evaluations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_evaluations_has_users_users1` 
              FOREIGN KEY (`evaluator_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `evaluation_groups`
              ADD CONSTRAINT `fk_evaluations_has_groups_evaluations1` 
              FOREIGN KEY (`evaluation_id`) 
              REFERENCES `evaluations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_evaluations_has_groups_groups1` 
              FOREIGN KEY (`group_id`) 
              REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `evaluation_questionnaires`
              ADD CONSTRAINT `fk_evaluations_has_questionnaires_evaluations1` 
              FOREIGN KEY (`evaluation_id`) 
              REFERENCES `evaluations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_evaluations_has_questionnaires_questionnaires1` 
              FOREIGN KEY (`questionnaire_id`) 
              REFERENCES `questionnaires` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `evaluation_sendings`
              ADD CONSTRAINT `fk_sendings_evaluations1` 
              FOREIGN KEY (`evaluation_id`) 
              REFERENCES `evaluations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `group_members`
              ADD CONSTRAINT `fk_groups_has_users_groups` 
              FOREIGN KEY (`group_id`) 
              REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_groups_has_users_users1` 
              FOREIGN KEY (`user_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `issues`
              ADD CONSTRAINT `fk_issues_scales1` 
              FOREIGN KEY (`scale_id`) 
              REFERENCES `scales` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_issues_users1` 
              FOREIGN KEY (`user_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `issue_alternatives`
              ADD CONSTRAINT `fk_issues_has_alternatives_alternatives1` 
              FOREIGN KEY (`alternative_id`) 
              REFERENCES `alternatives` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_issues_has_alternatives_issues1` 
              FOREIGN KEY (`issue_id`) 
              REFERENCES `issues` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `questionnaires`
              ADD CONSTRAINT `fk_questionnaires_users1` 
              FOREIGN KEY (`user_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `questionnaire_issues`
              ADD CONSTRAINT `fk_questionnaires_has_issues_issues1` 
              FOREIGN KEY (`issue_id`) 
              REFERENCES `issues` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_questionnaires_has_issues_questionnaires1` 
              FOREIGN KEY (`questionnaire_id`) 
              REFERENCES `questionnaires` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `scale_options`
              ADD CONSTRAINT `fk_scale_names_has_scale_rows_scale_names1` 
              FOREIGN KEY (`scale_id`) 
              REFERENCES `scales` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_scale_names_has_scale_rows_scale_rows1` 
              FOREIGN KEY (`scale_row_id`) 
              REFERENCES `scale_rows` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `sendings`
              ADD CONSTRAINT `fk_sendings_evaluation_sendings1` 
              FOREIGN KEY (`evaluation_sending_id`) 
              REFERENCES `evaluation_sendings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_sendings_users1` 
              FOREIGN KEY (`valued_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `sending_evaluators`
              ADD CONSTRAINT `fk_sending_evaluators_evaluation_sendings1` 
              FOREIGN KEY (`evaluation_sending_id`) 
              REFERENCES `evaluation_sendings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_sending_evaluators_sendings1` 
              FOREIGN KEY (`sending_id`) 
              REFERENCES `sendings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_sending_evaluators_users1` 
              FOREIGN KEY (`evaluator_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `user_charges`
              ADD CONSTRAINT `fk_users_has_charges_charges1` 
              FOREIGN KEY (`charge_id`) 
              REFERENCES `charges` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_users_has_charges_users1` 
              FOREIGN KEY (`user_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");

            $this->addSql("ALTER TABLE `user_ratings`
              ADD CONSTRAINT `fk_users_has_ratings_ratings1` 
              FOREIGN KEY (`rating_id`) 
              REFERENCES `ratings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
              ADD CONSTRAINT `fk_users_has_ratings_users1` 
              FOREIGN KEY (`user_id`) 
              REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('user_ratings');
        $schema->dropTable('user_charges');
        $schema->dropTable('users');
        $schema->dropTable('settings');
        $schema->dropTable('sending_evaluators');
        $schema->dropTable('sending_bcc');
        $schema->dropTable('sendings');
        $schema->dropTable('scale_rows');
        $schema->dropTable('scale_options');
        $schema->dropTable('scales');
        $schema->dropTable('ratings');
        $schema->dropTable('questionnaire_issues');
        $schema->dropTable('questionnaires');
        $schema->dropTable('pdfs');
        $schema->dropTable('models');
        $schema->dropTable('issue_alternatives');
        $schema->dropTable('issues');
        $schema->dropTable('group_members');
        $schema->dropTable('groups');
        $schema->dropTable('evaluation_sendings');
        $schema->dropTable('evaluation_questionnaires');
        $schema->dropTable('evaluation_groups');
        $schema->dropTable('evaluation_evaluators');
        $schema->dropTable('evaluations');
        $schema->dropTable('corrections');
        $schema->dropTable('charges');
        $schema->dropTable('averages');
        $schema->dropTable('answers');
        $schema->dropTable('alternatives');
    }
}
