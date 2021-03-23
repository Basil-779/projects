package com.acme.writers;

import static com.acme.Superapp.*;

public class WriterFabric {
    static public PersonWriter getWriterForFile(String path) {
        if (path.endsWith(CSV)) {
            return new PersonCSVWriter(path);
        } else if (path.endsWith(JSON)) {
            return new PersonJSONWriter(path);
        } else if (path.endsWith(XML)) {
            return new PersonXMLWriter(path);
        } else
            throw new RuntimeException("Unknown type of file");
    }
}
