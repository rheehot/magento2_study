<?php


namespace SimplifiedMagento\FirstModule\Model;


class Student
{

    private $name;
    private $age;

    public function __construct($name= "Alex", $age = 20, array $scores = array('maths'=>92, 'programming'=>90))
    {
    }
}