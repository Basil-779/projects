package com.acme.writers;

import com.acme.Person;

import java.io.IOException;
import java.io.Writer;
import java.util.List;

class PersonCSVWriter extends PersonWriter {

    public PersonCSVWriter(String path) {
        this.path = path;
    }

    public void write(List<Person> persons, Writer writer) throws IOException {
        StringBuilder buff = new StringBuilder();

        writer.write("Firstname;Lastname;Gender;Birthdate\n");
        for (Person p : persons) {
            buff.append(p.getFirstName()).append(";")
                    .append(p.getLastName()).append(";")
                    .append(p.getGender()).append(";")
                    .append(p.getBirthdate()).append("\n");
            writer.write(buff.toString());
            buff.delete(0, buff.length());
        }
    }
}
