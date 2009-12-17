
LOAD DATA INFILE '/Users/lobo/svn/sfschool/sql/JanToMay2010ExtendedDay.csv' INTO TABLE `sfschool_extended_care_source` FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY '\n' ;


UPDATE sfschool_extended_care_source SET name = '5th Grade Girls Basketball' WHERE name = '5th Grade Girls Basketball 3:30 - 5:00 pm';
UPDATE civicrm_value_extended_care_2 SET name = '5th Grade Girls Basketball' WHERE name = '5th Grade Girls Basketball 3:30 - 5:00 pm';
UPDATE civicrm_value_extended_care_signout_3 SET class = '5th Grade Girls Basketball' WHERE class = '5th Grade Girls Basketball 3:30 - 5:00 pm';

UPDATE sfschool_extended_care_source SET name = 'Middle School Basketball' WHERE name = 'Basketball Team 3:30-5:00 p.m.';
UPDATE civicrm_value_extended_care_2 SET name = 'Middle School Basketball' WHERE name = 'Basketball Team 3:30-5:00 p.m.';
UPDATE civicrm_value_extended_care_signout_3 SET class = 'Middle School Basketball' WHERE class = 'Basketball Team 3:30-5:00 p.m.';

INSERT INTO civicrm_value_extended_care_2 ( entity_id, name, term, instructor, description, fee_block, day_of_week, has_cancelled, start_date, end_date )
SELECT s.entity_id, s.name, 'Spring 2010', s.instructor, s.description, s.fee_block, s.day_of_week, 0, e.start_date, e.end_date
FROM   civicrm_value_extended_care_2 s
INNER JOIN sfschool_extended_care_source e ON ( s.name = e.name AND s.day_of_week = e.day_of_week AND e.term = 'Spring 2010' AND s.session = e.session )
WHERE  s.name IN ( 'Drumming', 'Violin', 'Guitar', 'Clarinet', 'Flute', 'Trumpet', 'Saxophone', 'Yearbook', 'Fencing', 'Golf',
                   'Academic Chess', 'Amnesty International', 'Middle School Basketball', '4th Grade Intramural Basketball',
                   '5th Grade Girls Basketball', '5th Grade Boys Basketball' )
AND    s.has_cancelled = 0;
