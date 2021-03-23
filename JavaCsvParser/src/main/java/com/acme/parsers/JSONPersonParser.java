package com.acme.parsers;

import com.acme.Person;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import org.json.simple.parser.JSONParser;
import org.json.simple.parser.ParseException;

import java.io.FileReader;
import java.io.IOException;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;
import java.util.stream.Stream;

class JSONPersonParser extends PersonParser {
    public JSONPersonParser(String path) {
        super(path);
    }

    @Override
    public List<Person> parse(Map<String, String> map) {
        this.map = map;
        JSONParser parser = new JSONParser();
        try (FileReader reader = new FileReader(path)) {
            JSONArray jsonArray = (JSONArray) parser.parse(reader);
            final Stream<Object> concat = jsonArray.stream();
            return concat
                    .map(o -> {
                        JSONObject json = (JSONObject) o;
                        return new Person(
                                (String) json.get("Firstname"),
                                (String) json.get("Lastname"),
                                (String) json.get("Gender"),
                                (String) json.get("Birthdate"));
                    })
                    .filter(this::checkGender)
                    .sorted(getComparator())
                    .collect(Collectors.toList());
        } catch (IOException | ParseException e) {
            throw new RuntimeException(e);
        }
    }
}
