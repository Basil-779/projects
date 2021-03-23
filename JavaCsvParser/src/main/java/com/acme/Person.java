package com.acme;

import java.text.SimpleDateFormat;
import java.util.Date;

public class Person {
    private String firstName;
    private String lastName;
    private String gender;
    private String birthdata;
    private Date date;

    private static String format = "yyyy-MM-dd";

    public Person(String firstName, String lastName, String gender, String birthdata) {
        this.firstName = firstName;
        this.lastName = lastName;
        this.gender = gender;
        this.birthdata = birthdata;

        try {
            date = new SimpleDateFormat(format).parse(birthdata);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    public Date getDate() {
        return date;
    }

    public String getBirthdate() {
        return birthdata;
    }

    public String getFirstName() {
        return firstName;
    }

    public String getGender() {
        return gender;
    }

    public String getLastName() {
        return lastName;
    }
}
