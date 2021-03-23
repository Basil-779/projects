package com.acme.parsers;

import java.util.Map;

import static com.acme.Superapp.*;
import static com.acme.Superapp.XML;

public class ParserFabric {
    public static PersonParser getPersonParser(String path) {
        if (path.endsWith(CSV))
            return new CSVPersonParser(path);
        else if (path.endsWith(JSON))
            return new JSONPersonParser(path);
        else if (path.endsWith(XML))
            return new XMLPersonParser(path);
        throw new RuntimeException("Unknown type of file");
    }
}
