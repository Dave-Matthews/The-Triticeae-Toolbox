/* increase size of marker sequence to aid primer design, 9/2017 */
alter table markers modify sequence varchar(512);

/* add updated_on to track changes, 8/2017 */
alter table linepanels add updated_on datetime default current_timestamp;
alter table allele_bymarker_expidx add updated_on datetime default current_timestamp;
alter table allele_byline_expidx add updated_on datetime default current_timestamp;

/* add option for private mapset, 10/2016  */

alter table mapset add column data_public_flag tinyint default 1 after published_on;

/* add coordinator and description field to experiment_set, 9/2016 */

alter table experiment_set add column coordinator varchar(250);
alter table experiment_set add column description varchar(512);

/* use same data type across all tables, 11/2015 */

alter table allele_bymarker modify column marker_uid int unsigned;
alter table allele_byline modify column line_record_uid int unsigned;
alter table allele_bymarker_idx modify column line_record_uid int unsigned;
alter table allele_byline_idx modify column marker_uid int unsigned;


