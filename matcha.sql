USE matcha;

INSERT INTO users (id, login, firstName, lastName, age, password, gender, email, hash, bio, sexuality, rating, longitude, latitude) VALUES
(1, 'Moscow', 'Vladimir', 'Putin', 67, '1', 'M', '1', '1', 'Moscow is the capital and most populous city of Russia. 
With over 12 million residents living within the city limits of 2,511 square kilometres (970 sq mi) as of 2019, Moscow is among the worlds 
largest cities, being the second-most populous city in Europe, the most populous city entirely within Europe, and also the largest city (by area) 
on the European continent.', 'heterosexual', 100, 37.61556, 55.75222),
(2, 'Paris', 'Emmanuel Jean-Michel Frédéric', 'Macron', 42, '1', 'M', '1', '1', 'The City of Paris is the centre and seat of government of the Île-de-France, 
or Paris Region, which has an estimated official 2020 population of 12,278,210, or about 18 percent of the population of France. The Paris Region had a GDP 
of €709 billion ($808 billion) in 2017. According to the Economist Intelligence Unit Worldwide Cost of Living Survey in 2018, Paris was the second most 
expensive city in the world, after Singapore, and ahead of Zürich, Hong Kong, Oslo and Geneva.Another source ranked Paris as most expensive, on a par 
with Singapore and Hong Kong, in 2018.', 'bisexual', 26, 2.352221, 48.856614),
(3, 'Warsaw', 'Andjei', 'Duda', 47, '1', 'M', '1', '1', 'is the capital and largest city of Poland. The metropolis stands on the Vistula River in east-central 
Poland and its population is officially estimated at 1.8 million residents within a greater metropolitan area of 3.1 million residents, which makes Warsaw 
the 7th most-populous capital city in the European Union. The city limits cover 517.24 square kilometres (199.71 sq mi), while the metropolitan area 
covers 6,100.43 square kilometres (2,355.39 sq mi). Warsaw is an alpha global city, a major international tourist destination, and a significant cultural, 
political and economic hub. Its historical old town was designated a UNESCO World Heritage Site', 'heterosexual', 2, 21.012228, 52.229675),
(4, 'Riga', 'Egil', 'Levits', 64, '1', 'F', '1', '1', 'Riga is the capital of Latvia and is home to 632,614 inhabitants (2019), 
which is a third of Latvia s population. Being significantly larger than other cities of Latvia, Riga is the country s primate city. 
It is also the largest city in the three Baltic states and is home to one tenth of the three Baltic states combined population. 
The city lies on the Gulf of Riga at the mouth of the Daugava river where it meets the Baltic Sea. Riga s territory covers 307.17 km2 (118.60 sq mi) 
and lies 1–10 m (3 ft 3 in–32 ft 10 in) above sea level, on a flat and sandy plain', 'homosexual', 67, 24.105186, 56.949648),
(5, 'Berlin', 'Frank-Walter', 'Steinmeier', 63, '1', 'F', '1', '1', 'Berlin  is the capital and largest city of Germany by both area and population. 
Its 3,748,148 (2018) inhabitants make it the most populous city proper of the European Union. The city is one of Germany s 16 federal states. 
It is surrounded by the state of Brandenburg, and contiguous with Potsdam, Brandenburgs capital. The two cities are at the center of the Berlin-Brandenburg capital region, 
which is, with about six million inhabitants and an area of more than 30,000 km², Germany s third-largest metropolitan region after the Rhine-Ruhr 
and Rhine-Main regions.', 'heterosexual', 85, 13.404954, 52.520006),
(6, 'London', 'Elizabeth', 'The second', 93, '1', 'F', '1', '1', 'London is the capital and largest city of England and the United Kingdom. 
Standing on the River Thames in the south-east of England, at the head of its 50-mile (80 km) estuary leading to the North Sea, London has been a major settlement for two millennia. 
Londinium was founded by the Romans. The City of London, London s ancient core − an area of just 1.12 square miles (2.9 km2) and colloquially known 
as the Square Mile − retains boundaries that closely follow its medieval limits. The City of Westminster is also an Inner London borough holding city status. 
Greater London is governed by the Mayor of London and the London Assembly', 'homosexual', 29, -0.127758, 51.507350),
(7, 'Athens', 'Katherina', 'Sakellaropulu', 63, '1', 'F', '1', '1', 'Athens is the capital and largest city of Greece. 
Athens dominates the Attica region and is one of the world s oldest cities, with its recorded history spanning over 3,400 years
 and its earliest human presence started somewhere between the 11th and 7th millennium BC', 'bisexual', 18, 23.729359, 37.983917),
(8, 'Rome', 'Sergio', 'Mattarella', 78, '1', 'F', '1', '1', 'Rome  is the capital city and a special comune of Italy (named Comune di Roma Capitale). 
Rome also serves as the capital of the Lazio region. With 2,879,728 residents in 1,285 km2 (496.1 sq mi), it is also the countrys most populated comune. 
It is the third most populous city in the European Union by population within city limits. It is the centre of the Metropolitan City of Rome, 
which has a population of 4,355,725 residents, thus making it the second or third most populous metropolitan city in Italy depending on definition. 
Rome is located in the central-western portion of the Italian Peninsula, within Lazio (Latium), along the shores of the Tiber. 
Vatican City (the smallest country in the world) is an independent country inside the city boundaries of Rome, 
the only existing example of a country within a city; for this reason Rome has sometimes been defined as the capital of two states', 'heterosexual', 41, 12.496365, 41.902783),
(9, 'New-York', 'Donald', 'Trump', 73, '1', 'M', '1', '1', 'NYC  is the most populous city in the United States. 
With an estimated 2018 population of 8,398,748 distributed over about 302.6 square miles (784 km2), New York is also the most densely populated major city in the United States. 
Located at the southern tip of the U.S. state of New York, the city is the center of the New York metropolitan area, 
the largest metropolitan area in the world by urban landmass. With almost 20 million people in its metropolitan statistical area and approximately 23 million in its combined statistical area, 
it is one of the worlds most populous megacities. New York City has been described as the cultural, financial, and media capital of the world, 
significantly influencing commerce, entertainment, research, technology, education, politics, tourism, art, fashion, and sports. Home to the headquarters of the United Nations, 
New York is an important center for international diplomacy', 'heterosexual', 59, -74.005941, 40.712783),
(10, 'Sydney', 'Scott', 'Morrison', 51, '1', 'M', '1', '1', 'Sydney  is the state capital of New South Wales and the most populous city in Australia and Oceania. Located on Australias east coast, 
the metropolis surrounds Port Jackson and extends about 70 km (43.5 mi) on its periphery towards the Blue Mountains to the west, Hawkesbury to the north, 
the Royal National Park to the south and Macarthur to the south-west. Sydney is made up of 658 suburbs, 40 local government areas and 15 contiguous regions. 
Residents of the city are known as "Sydneysiders". As of June 2017, Sydneys estimated metropolitan population was 5,230,330 and is home to approximately 65% 
of the states population.', 'homosexual', 68, 151.20699, -33.867486),
(11, 'Mumbai', 'Ram Nath', 'Covind', 74, '1', 'F', '1', '1', 'According to United Nations, as of 2018, Mumbai was the second most populous city in India after 
Delhi and the seventh most populous city in the world with a population of 19.98 million. As per Indian government population census of 2011, 
Mumbai was the most populous city in India with an estimated city proper population of 12.5 million living under Municipal Corporation of Greater Mumbai. 
Mumbai is the centre of the Mumbai Metropolitan Region, the sixth most populous metropolitan area in the world with a population of over 23.64 million. 
Mumbai lies on the Konkan coast on the west coast of India and has a deep natural harbour. In 2008, Mumbai was named an alpha world city. 
It is also the wealthiest city in India, and has the highest number of millionaires and billionaires among all cities in India. Mumbai is home to three UNESCO World Heritage Sites: the Elephanta Caves, 
Chhatrapati Shivaji Maharaj Terminus, and the citys distinctive ensemble of Victorian and Art Deco buildings.', 'bisexual', 99, 72.877655, 19.075983),
(12, 'Beijin', 'Si', 'Tzinpin', 66, '1', 'M', '1', '1', 'Beijin  is the capital of the Peoples Republic of China. It is the worlds most populous capital city, 
with over 21 million residents within an administrative area of 16,410.5 km2. The city, located in northern China, is governed as a municipality under the direct administration of the central government with 16 urban, 
suburban, and rural districts. Beijing is mostly surrounded by Hebei Province with the exception of neighboring Tianjin to the southeast; together, 
the three divisions form the Jingjinji megalopolis and the national capital region of China.', 'heterosexual', 71, 116.407395, 39.904211),
(13, 'Vladivostok', 'Dmitry', 'Medvedev', 54, '1', 'M', '1', '1', 'Vladivostok is a city and the administrative centre of the Far Eastern Federal District and Primorsky Krai, Russia, 
located around the Golden Horn Bay, not far from Russias borders with China and North Korea. The population of the city as of 2019 was 605,049 up from 592,034 
recorded in the 2010 Russian census. Harbin in China is about 515 kilometres (320 mi) away, while Sapporo in Japan is about 775 kilometres (482 mi) east across the Sea of Japan.', 'bisexuall', 14, 131.9, 43.133333),
(14, 'Cape town', 'Matamela Cyril', 'Ramaphosa', 67, '1', 'F', '1', '1', 'Cape Town is the second most populous city in South Africa after Johannesburg and also the legislative capital of South Africa. 
Colloquially named the Mother City, it is the largest city of the Western Cape province and forms part of the City of Cape Town metropolitan municipality. 
The Parliament of South Africa sits in Cape Town. The other two capitals are located in Pretoria (the executive capital where the Presidency is based) and Bloemfontein 
(the judicial capital where the Supreme Court of Appeal is located). The city is known for its harbour, for its natural setting in the Cape Floristic Region, and for landmarks such as Table Mountain and Cape Point. 
Cape Town is home to 64% of the Western Capes population', 'heterosexual', 81, 18.424055, -33.924868),
(15, 'Cairo', 'Khalil', 'As-Sisi', 65, '1', 'F', '1', '1', 'is the capital of Egypt and the largest city in the Arab world. Its metropolitan area, with a population of over 20 million, is the largest in Africa, the Arab world, 
and the Middle East, and the 15th-largest in the world. Cairo is associated with ancient Egypt, as the famous Giza pyramid complex and the ancient city of Memphis are located in its geographical area. 
Located near the Nile Delta, Cairo was founded in 969 AD by the Fatimid dynasty, 
but the land composing the present-day city was the site of ancient national capitals whose remnants remain visible in parts of Old Cairo. Cairo has long been a centre of the regions political and cultural life, 
and is titled "the city of a thousand minarets" for its preponderance of Islamic architecture. Cairo is considered a World City with a "Beta +" classification according to GaWC.', 'heterosexual', 21, 31.235711, 30.044419),
(16, 'Brasilia', 'Jair', 'Bolsonaro', 64, '1', 'M', '1', '1', 'Brasilia is the federal capital of Brazil and seat of government of the Federal District. The city is located atop the Brazilian highlands in the countrys center-western region. 
It was founded on April 21, 1960, to serve as the new national capital. Brasília is estimated to be Brazils third-most populous city. 
Among major Latin American cities, it has the highest GDP per capita', 'heterosexual', 43, -47.882165, -15.794228),
(17, 'Astana', 'Kasym-Jomar', 'Tokaev', 66, '1', 'M', '1', '1', 'Astana(Nur-Sultan) is the capital city of Kazakhstan. In March 2019, it was renamed to Nur-Sultan after the departing Kazakh president, Nursultan Nazarbayev. 
It stands on the banks of the Ishim River in the northern portion of Kazakhstan, within the Akmola Region, though administered separately from the region as a city with special status. 
A 2017 official estimate reported a population of 1,029,556 within the city limits, making it the second-largest city in the country, behind Almaty, the capital from 1991 to 1997', 'bisexual', 71, 71.470355, 51.160522),
(18, 'Ulan Bator', 'Battulga', 'Haltmaygin', 57, '1', 'F', '1', '1', 'Ulan Bator is the capital and largest city of Mongolia. The city is not part of any aimag (province), and its population as of 2014 was over 1.3 million, 
almost half of the countryss population. The municipality is in north central Mongolia at an elevation of about 1,300 meters (4,300 ft) in a valley on the Tuul River. It is the countrys cultural, industrial and financial heart, 
the centre of Mongolias road network and connected by rail to both the Trans-Siberian Railway in Russia and the Chinese railway system', 'heterosexual', 92, 106.92, 47.92),
(19, 'Santiago', 'Sebastyan', 'Pernera', 70, '1', 'M', '1', '1', 'Santiago is the capital and largest city of Chile as well as one of the largest cities in the Americas. It is the center of Chiles largest and most densely populated conurbation, 
the Santiago Metropolitan Region, whose total population is 7 million. The city is entirely located in the countrys central valley. Most of the city lies between 500 m (1,640 ft) and 650 m (2,133 ft) above mean sea level.', 'homosexual', 19, -70.641997, -33.469119),
(20, 'Helsinki', 'Väinämö Niinistö', 'Sauli', 71, '1', 'F', '1', '1', 'Helsinki is the capital and most populous city of Finland. Located on the shore of the Gulf of Finland, it is the seat of the region of Uusimaa in southern Finland, and has a population of 650,058. 
The citys urban area has a population of 1,268,296, making it by far the most populous urban area in Finland as well as the countrys most important center for politics, education, finance, culture, and research. 
Helsinki is located 80 kilometres (50 mi) north of Tallinn, Estonia, 400 km (250 mi) east of Stockholm, Sweden, and 300 km (190 mi) west of Saint Petersburg, Russia. It has close historical ties with these three cities.', 'heterosexual', 32, 24.941024, 60.173324);

INSERT INTO tags (id, id_belong, algorythm, graphics, unix, sysadmin, web) VALUES
(1, 1, 1, 0, 1, 0, 0),
(2, 2, 1, 0, 0, 1, 0),
(3, 3, 0, 1, 1, 1, 0),
(4, 4, 1, 0, 0, 0, 1),
(5, 5, 0, 1, 1, 0, 1),
(6, 6, 1, 0, 1, 0, 1),
(7, 7, 1, 1, 1, 0, 1),
(8, 8, 0, 1, 0, 0, 0),
(9, 9, 1, 1, 1, 1, 1),
(10, 10, 0, 1, 0, 1, 0),
(11, 10, 1, 0, 1, 0, 0),
(12, 12, 1, 0, 0, 1, 1),
(13, 13, 0, 1, 1, 1, 0),
(14, 14, 1, 0, 0, 0, 1),
(15, 15, 0, 1, 1, 0, 1),
(16, 16, 1, 1, 1, 1, 1),
(17, 17, 0, 1, 0, 0, 1),
(18, 18, 1, 0, 1, 0, 0),
(19, 19, 1, 1, 1, 0, 1),
(20, 20, 1, 1, 0, 1, 0);

INSERT INTO blocks (id, id_belong, id_blocker) VALUES
(1, 2, 19),
(2, 4, 10),
(3, 11, 2),
(4, 16, 3);