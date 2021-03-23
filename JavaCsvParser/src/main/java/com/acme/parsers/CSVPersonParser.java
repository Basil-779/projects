package com.acme.parsers;

import com.acme.Person;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

class CSVPersonParser extends PersonParser {
    public CSVPersonParser(String path) {
        super(path);
    }

    @Override
    public List<Person> parse(Map<String, String> map) {
        this.map = map;
        try (BufferedReader fin = new BufferedReader(new FileReader(path))) {
            return fin.lines().skip(1)
                    .map(s -> {
                        String args[] = s.split(";");
                        return new Person(args[0], args[1], args[2], args[3]);
                    })
                    .filter(this::checkGender)
                    .sorted(getComparator())
                    .collect(Collectors.toList());
        } catch (IOException e) {
            throw new RuntimeException(e);
        }
    }
}
