package com.acme.writers;

import com.acme.Person;

import java.io.IOException;
import java.io.Writer;
import java.util.List;

class PersonXMLWriter extends PersonWriter {

    public PersonXMLWriter(String path) {
        this.path = path;
    }

    public void write(List<Person> persons, Writer writer) throws IOException {
        StringBuilder buff = new StringBuilder();

        writer.write("<?xml version = \"1.0\" encoding=\"UTF-8\"?>\n<body>\n");
        for (Person p : persons) {
            buff.append("<person>\n")
                    .append("<Firstname>").append(p.getFirstName()).append("</Firstname>\n")
                    .append("<Lastname>").append(p.getLastName()).append("</Lastname>\n")
                    .append("<Gender>").append(p.getGender()).append("</Gender>\n")
                    .append("<Birthdate>").append(p.getBirthdate()).append("</Birthdate>\n")
                    .append("</person>\n");
            writer.write(buff.toString());
            buff.delete(0, buff.length());
        }
        writer.write("</body>\n");
    }
}
