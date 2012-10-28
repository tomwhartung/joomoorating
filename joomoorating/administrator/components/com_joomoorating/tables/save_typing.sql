#
# SQL statements to help save typing when querying and making changes
#

INSERT INTO jos_joomoorating set contentid = 128, galleryimageid = 0, vote_count = 10, vote_total = 42;
INSERT INTO jos_joomoorating set contentid = 0, galleryimageid = 49, vote_count = 14, vote_total = 42;

UPDATE jos_joomoorating set vote_count = vote_count+1, vote_total = vote_total+4 WHERE id = 1;
