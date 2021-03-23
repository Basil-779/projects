package com.acme.parsers;

import com.acme.Person;
import java.util.*;

import static com.acme.Superapp.*;

public abstract class PersonParser {
    final static String MAN = "m";
    final static String WOMAN = "f";

    Map<String, String> map;
    String path;

    public PersonParser(String path) {
        this.path = path;
    }

    boolean checkGender(Person person) {
        if (map.containsKey(MALES) && TRUE.equals(map.get(MALES))) {
            return MAN.equals(person.getGender());
        }
        if (map.containsKey(FEMALES) && TRUE.equals(map.get(FEMALES))) {
            return WOMAN.equals(person.getGender());
        }
        return true;
    }

    Comparator<Person> getComparator() {
        Comparator<Person> comparator = Comparator.comparing(Person::getDate, Date::compareTo);
        if (map.containsKey(NAME_ORDER))
            comparator = Comparator.comparing(Person::getFirstName).thenComparing(comparator);
        if (map.containsKey(REVERS))
            comparator = comparator.reversed();
        return comparator;
    }

    abstract public List<Person> parse(Map<String, String> map);
}

