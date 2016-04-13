<?php
$installer = $this;

$installer->startSetup();

$table = $installer->getTable('testimonialmanager');

$installer->run("
    create table IF NOT EXISTS {$table} (
        testimonial_id int(11) unsigned not null auto_increment,
        testimonial_position int(11) default 0,
        testimonial_name varchar(50) not null default '',
        testimonial_text text not null default '',
        testimonial_img varchar(128) default NULL,
        testimonial_sidebar tinyint(4) NOT NULL default 2,
        testimonial_company varchar(50) not null default '',
        testimonial_email varchar(50) not null default '',
        testimonial_website varchar(50) not null default '',
        status tinyint(4) NOT NULL default 3,
        rating_summary tinyint(4) NOT NULL default 0,
        PRIMARY KEY(testimonial_id)
    ) engine=InnoDB default charset=utf8;
");

$installer->endSetup();