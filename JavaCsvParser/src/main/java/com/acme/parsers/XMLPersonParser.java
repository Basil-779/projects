package com.acme.parsers;

import com.acme.Person;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

class XMLPersonParser extends PersonParser {
    public XMLPersonParser(String path) {
        super(path);
    }


    private String getTag(Element element, String tagName) {
        return element.getElementsByTagName(tagName).item(0).getTextContent();
    }

    private Person createPerson(Node node) {
        Element p = (Element) node;
        return new Person(
                getTag(p, "Firstname"),
                getTag(p, "Lastname"),
                getTag(p, "Gender"),
                getTag(p, "Birthdate"));
    }

    @Override
    public List<Person> parse(Map<String, String> map) {
        this.map = map;
        List<Person> list = new ArrayList<>();

        try {
            DocumentBuilder builder = DocumentBuilderFactory.newInstance().newDocumentBuilder();
            Document doc = builder.parse(path);

            Element root = doc.getDocumentElement();
            NodeList persons = root.getChildNodes();
            for (int i = 0; i < persons.getLength(); i++) {
                Node node = persons.item(i);
                if (!node.hasChildNodes())
                    continue;
                list.add(createPerson(node));
            }

            return list.stream()
                    .filter(this::checkGender)
                    .sorted(getComparator())
                    .collect(Collectors.toList());
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
}
