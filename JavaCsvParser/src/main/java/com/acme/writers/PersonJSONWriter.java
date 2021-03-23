package com.acme.writers;

import com.acme.Person;

import java.io.IOException;
import java.io.Writer;
import java.util.List;

class PersonJSONWriter extends PersonWriter {

    public PersonJSONWriter(String path) {
        this.path = path;
    }

    public void write(List<Person> persons, Writer writer) throws IOException {
        StringBuilder buff = new StringBuilder();

        writer.write("[\n");
        for (int i = 0; i < persons.size(); i++) {
            Person p = persons.get(i);
            buff.append("{\"Firstname\":\"").append(p.getFirstName()).append("\",")
                    .append("\"Lastname\":\"").append(p.getLastName()).append("\",")
                    .append("\"Gender\":\"").append(p.getGender()).append("\",")
                    .append("\"Birthdate\":\"").append(p.getBirthdate()).append("\"}");
            if (i < persons.size() - 1)
                buff.append(",");
            buff.append("\n");
            writer.write(buff.toString());
            buff.delete(0, buff.length());
        }
        writer.write("]\n");
    }
}
