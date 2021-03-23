package com.acme;


import com.acme.parsers.ParserFabric;
import com.acme.writers.WriterFabric;

import java.util.*;


public class Superapp {

    public final static String IN = "--in";
    public final static String OUT = "--out";
    public static final String TOP = "--top";
    public static final String LAST = "--last";
    public static final String MALES = "--males-only";
    public static final String FEMALES = "--females-only";
    public static final String REVERS = "--reverse-order";
    public static final String NAME_ORDER = "--name-order";

    public static final String TRUE = "true";

    public static final String CSV = ".csv";
    public static final String XML = ".xml";
    public static final String JSON = ".json";

    private Map<String, String> map;
    private List<Person> persons;

    public Superapp(String args[]) {
        map = new HashMap<>();
        parseAndValidateOption(args);
    }

    public void setPersons(List<Person> persons) {
        this.persons = persons;
    }

    public List<Person> getPersons() {
        return persons;
    }

    public Map<String, String> getOptions() {
        return map;
    }

    public String getIn() {
        return map.get(IN);
    }

    public String getOut() {
        return map.get(OUT);
    }

    private void parseAndValidateOption(String args[]) {
        int i = 0;
        while (i < args.length) {
            if (map.containsKey(args[i]))
                throw new RuntimeException("repeat arg: " + args[i]);
            switch (args[i]) {
                case IN:
                case OUT:
                case TOP:
                case LAST:
                case MALES:
                case FEMALES:
                    map.put(args[i], args[i + 1]);
                    i += 2;
                    break;
                case REVERS:
                case NAME_ORDER:
                    map.put(args[i], "true");
                    i += 1;
                    break;
                default:
                    throw new RuntimeException("invalid arg: " + args[i]);
            }
        }

        //проверяем наличие обязательных опций
        if (!map.containsKey(IN))
            throw new RuntimeException("no options --in");
        if (!map.containsKey(OUT))
            throw new RuntimeException("no options --out");
    }

    private int intOf(String str) {
        return Integer.parseInt(map.get(str));
    }

    private void applayOption() {
        if (!map.containsKey(LAST) && !map.containsKey(TOP)) {
            return;
        } else if (map.containsKey(TOP) && map.containsKey(LAST)
                && intOf(TOP) + intOf(LAST) >= persons.size()) {
            return;
        } else {
            List<Person> list = persons;
            persons = new ArrayList<>();

            if (map.containsKey(TOP)) {
                int n = Math.min(intOf(TOP), list.size());
                persons.addAll(list.subList(0, n));
            }
            if (map.containsKey(LAST)) {
                int n = Math.max(0, list.size() - intOf(LAST));
                persons.addAll(list.subList(n, list.size()));
            }
        }
    }

    public static void main(String[] args) {
        // put your code here
        Superapp app = new Superapp(args);
        app.setPersons(ParserFabric.getPersonParser(app.getIn()).parse(app.getOptions()));
        app.applayOption();
        WriterFabric.getWriterForFile(app.getOut()).write(app.getPersons());
    }
}
