USE matcha;

INSERT INTO users (id, login, firstName, lastName, age, password, gender, email, hash, bio, sexuality, longitude, latitude) VALUES
(1, 'Moscow', 'Moscow', 'Moscow', 1, '1', 'M', '1', '1', '1', 'heterosexual', 37.61556, 55.75222),
(2, 'Paris', 'Paris', 'Paris', 1, '1', 'F', '1', '1', '1', 'bisexual', 2.352221, 48.856614),
(3, 'Warsaw', 'Warsaw', 'Warsaw', 1, '1', 'M', '1', '1', '1', 'heterosexual', 21.012228, 52.229675),
(4, 'Riga', 'Riga', 'Riga', 1, '1', 'F', '1', '1', '1', 'homosexual', 24.105186, 56.949648),
(5, 'Berlin', 'Berlin', 'Berlin', 1, '1', 'F', '1', '1', '1', 'heterosexual', 13.404954, 52.520006),
(6, 'London', 'London', 'London', 1, '1', 'M', '1', '1', '1', 'homosexual', -0.127758, 51.507350),
(7, 'Athens', 'Athens', 'Athens', 1, '1', 'F', '1', '1', '1', 'bisexual', 23.729359, 37.983917),
(8, 'Rome', 'Rome', 'Rome', 1, '1', 'F', '1', '1', '1', 'heterosexual', 12.496365, 41.902783),
(9, 'New-York', 'New-York', 'New-York', 1, '1', 'M', '1', '1', '1', 'heterosexual', -74.005941, 40.712783),
(10, 'Sydney', 'Sydney', 'Sydney', 1, '1', 'M', '1', '1', '1', 'homosexual', 151.20699, -33.867486),
(11, 'Mumbai', 'Mumbai', 'Mumbai', 1, '1', 'F', '1', '1', '1', 'bisexual', 72.877655, 19.075983),
(12, 'Beijin', 'Beijin', 'Beijin', 1, '1', 'M', '1', '1', '1', 'heterosexual', 116.407395, 39.904211),
(13, 'Vladivostok', 'Vladivostok', 'Vladivostok', 1, '1', 'M', '1', '1', '1', 'bisexuall', 131.9, 43.133333),
(14, 'Capetown', 'Capetown', 'Capetown', 1, '1', 'F', '1', '1', '1', 'heterosexual', 18.424055, -33.924868),
(15, 'Cairo', 'Cairo', 'Cairo', 1, '1', 'F', '1', '1', '1', 'heterosexual', 31.235711, 30.044419),
(16, 'Brazilia', 'Brazilia', 'Brazilia', 1, '1', 'M', '1', '1', '1', 'heterosexual', -47.882165, -15.794228),
(17, 'Astana', 'Astana', 'Astana', 1, '1', 'M', '1', '1', '1', 'bisexual', 71.470355, 51.160522),
(18, 'Ulan-Bator', 'Ulan-Bator', 'Ulan-Bator', 1, '1', 'F', '1', '1', '1', 'heterosexual', 106.92, 47.92),
(19, 'Santyago', 'Santyago', 'Santyago', 1, '1', 'M', '1', '1', '1', 'homosexual', -70.641997, -33.469119),
(20, 'Helsinki', 'Helsinki', 'Helsinki', 1, '1', 'F', '1', '1', '1', 'heterosexual', 24.941024, 60.173324);

INSERT INTO tags (id, id_belong, algorythm, graphics, unix, sysadmin, web) VALUES
(1, 1, 1, 0, 1, 0, 0),
(2, 2, 1, 0, 0, 1, 0),
(3, 3, 0, 1, 1, 1, 0),
(4, 4, 1, 0, 0, 0, 1),
(5, 5, 0, 1, 1, 0, 1),
(6, 6, 0, 0, 0, 0, 1),
(7, 7, 0, 1, 1, 0, 1),
(8, 8, 0, 1, 0, 0, 0),
(9, 9, 1, 1, 1, 1, 1),
(10, 10, 0, 1, 0, 1, 0),
(11, 10, 1, 0, 1, 0, 0),
(12, 12, 1, 0, 0, 1, 1),
(13, 13, 0, 1, 1, 1, 0),
(14, 14, 0, 0, 0, 0, 1),
(15, 15, 0, 1, 1, 0, 1),
(16, 16, 1, 1, 0, 1, 1),
(17, 17, 0, 1, 0, 0, 1),
(18, 18, 0, 0, 1, 0, 0),
(19, 19, 1, 1, 1, 0, 1),
(20, 20, 1, 1, 0, 1, 0);

INSERT INTO blocks (id, id_belong, id_blocker) VALUES
(1, 2, 19),
(2, 4, 10),
(3, 11, 2),
(4, 16, 3);