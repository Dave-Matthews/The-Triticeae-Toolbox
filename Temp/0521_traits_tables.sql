CREATE TABLE gramene (
  gramene_uid INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  phenotype_uid INTEGER UNSIGNED NOT NULL,
  term VARCHAR(255) NULL,
  definition TEXT NULL,
  created_on DATETIME NULL,
  updated_on DATETIME NULL,
  PRIMARY KEY(gramene_uid)
);

CREATE TABLE phenotypes (
  phenotype_uid INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  phenotype_category_id INTEGER UNSIGNED NOT NULL,
  unit_id INTEGER UNSIGNED NOT NULL,
  name VARCHAR(255) NULL,
  short_name VARCHAR(10) NULL,
  description TEXT NULL,
  datatype ENUM('DOUBLE', 'CHAR', 'INTEGER') NULL,
  created_on DATETIME NULL,
  updated_on DATETIME NULL,
  PRIMARY KEY(phenotype_uid)
)
TYPE=InnoDB;

CREATE TABLE phenotype_category (
  phenotype_category_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(45) NULL,
  created_on DATETIME NULL,
  updated_on DATETIME NULL,
  PRIMARY KEY(phenotype_category_id)
)
TYPE=InnoDB;

CREATE TABLE phenotype_descstat (
  phenotype_descstat_id INTEGER UNSIGNED NOT NULL,
  phenotype_uid INTEGER UNSIGNED NOT NULL,
  mean_val DOUBLE NULL,
  max_val DOUBLE NULL,
  min_val DOUBLE NULL,
  sample_size INTEGER UNSIGNED NULL,
  std DOUBLE NULL,
  created_on DATETIME NULL,
  updated_on DATETIME NULL,
  PRIMARY KEY(phenotype_descstat_id)
)
TYPE=InnoDB;

CREATE TABLE units (
  unit_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  unit_name VARCHAR(255) NOT NULL,
  unit_abbreviation VARCHAR(255) NOT NULL,
  unit_description VARCHAR(255) NOT NULL,
  created_on DATETIME NULL,
  updated_on DATETIME NULL,
  PRIMARY KEY(unit_id)
);


