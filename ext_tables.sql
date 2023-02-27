#
# Modifying pages table
#
CREATE TABLE pages (
    tx_tileproxy_flexform mediumtext DEFAULT '',
);

#
# Table structure for table 'tx_tileproxy_domain_model_requestcache'
#
CREATE TABLE tx_tileproxy_domain_model_requestcache (
  'url_hash' varchar(512) DEFAULT '' NOT NULL,
  'data' mediumblob NOT NULL,
  'created' int(11) NOT NULL,
   UNIQUE KEY `key_url_hash` (`url_hash`)
);