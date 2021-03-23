package com.acme.writers;

import com.acme.Person;

import java.io.BufferedWriter;
import java.io.FileWriter;
import java.io.IOException;
import java.io.Writer;
import java.util.List;

public abstract class PersonWriter {
    String path;

    public void write(List<Person> persons) {
        try (final BufferedWriter fout = new BufferedWriter(new FileWriter(path))) {
            write(persons, fout);
        } catch (IOException ex) {
            System.out.println(ex.getMessage());
        }
    }

    public abstract void write(List<Person> persons, Writer writer) throws IOException;
}

