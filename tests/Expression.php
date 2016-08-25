<?php

use PHPUnit\Framework\TestCase;



class ExpressionTest extends TestCase {
    public function arrayTest($array) {
        $expr = new Expression();
        for ($i=0; $i<count($array); $i++) {
            $result = $expr->evaluate($array[$i]);
            $this->assertEquals($result, eval("return " . $array[$i] . ";"));
        }
    }
    // -------------------------------------------------------------------------
    public function testIntegers() {
        $ints = array("100", "3124123", (string)PHP_INT_MAX, "-1000");
        $expr = new Expression();
        for ($i=0; $i<count($ints); $i++) {
            $result = $expr->evaluate($ints[$i]);
            $this->assertEquals($result, intval($ints[$i]));
        }

    }
    // -------------------------------------------------------------------------
    public function testFloats() {
        $ints = array("10.10", "0.01", "-100.100", "1.10e2", "-0.10e10");
        $expr = new Expression();
        for ($i=0; $i<count($ints); $i++) {
            $result = $expr->evaluate($ints[$i]);
            $this->assertEquals($result, floatval($ints[$i]));
        }
    }
    // -------------------------------------------------------------------------
    public function testAritmeticOperators() {
        $expressions = array("20+20", "-20+20", "-0.1+0.1", "20*20", "20-20",
                             "20/20");
        $this->arrayTest($expressions);
    }
    // -------------------------------------------------------------------------
    public function testSemicolon() {
        $expr = new Expression();
        $result = $expr->evaluate("10+10;");
        $this->assertEquals($result, "20");
    }
    // -------------------------------------------------------------------------
    public function testBooleanComparators() {
        $expressions = array("10 == 10", "10 == 20", "0.1 == 0.1", "0.1 == 0.2",
                             "10 != 10", "20 != 10", "0.1 != 0.1", "0.1 != 0.2",
                             "10 < 10", "20 < 10", "10 < 20", "0.1 < 0.2",
                             "0.2 < 0.1", "0.1 < 0.1", "10 > 10", "20 > 10",
                             "10 > 20", "0.1 > 0.2", "0.2 > 0.1", "0.1 > 0.1",
                             "10 <= 10", "20 <= 10", "10 <= 20", "0.1 <= 0.2",
                             "0.2 <= 0.1", "0.1 <= 0.1", "10 >= 10", "20 >= 10",
                             "10 >= 20", "0.1 >= 0.2", "0.2 >= 0.1", "0.1 >= 0.1");
        $this->arrayTest($expressions);
    }
    // -------------------------------------------------------------------------
    public function testBooleanOperators() {
        $expressions = array("10 == 10 && 10 == 10", "10 != 10 && 10 != 10",
                             "10 == 20 && 10 == 10", "10 == 10 && 10 == 20",
                             "0.1 == 0.1 && 0.1 == 0.1", "0.1 == 0.2 && 0.1 == 0.1",
                             "0.1 == 0.1 && 0.1 == 0.2", "10 == 10 || 10 == 10",
                             "10 == 20 || 10 == 10", "10 == 10 || 10 == 20",
                             "0.1 == 0.1 || 0.1 == 0.1", "0.1 == 0.2 || 0.1 == 0.1",
                             "0.1 == 0.1 || 0.1 == 0.2");
        $this->arrayTest($expressions);
    }
    // -------------------------------------------------------------------------
    public function testNegation() {
        $expressions = array("!(10 == 10)", "!1", "!0");
        $this->arrayTest($expressions);
    }
    // -------------------------------------------------------------------------
    public function testStrings() {
        $expressions = array('"foo" == "foo"', '"foo\\"bar" == "foo\\"bar"',
                             '"f\\"oo" != "f\\"oo"', '"foo\\"" != "foo\\"bar"');
        $this->arrayTest($expressions);
    }
    // -------------------------------------------------------------------------
    public function testMatchers() {
        $expressions = array('"foobar" =~ "/([fo]+)/"' => 'foo',
                             '"foobar" =~ "/([0-9]+)/"' => null,
                             '"1020" =~ "/([0-9]+)/"'=> '1020',
                             '"1020" =~ "/([a-z]+)/"' => null);
        
        foreach ($expressions as $expression => $group) {
            $expr = new Expression();
            $result = $expr->evaluate($expression);
            if ($group == null) {
                $this->assertEquals((boolean)$result, $group != null);
            }
            if ($group != null) {
                $this->assertEquals($expr->evaluate('$1'), $group);
            }
        }
    }
    // -------------------------------------------------------------------------
    public function testVariableAssignment() {
        $expressions = array('foo = "bar"' => array('var' => 'foo', 'value' => 'bar'),
                             'foo = 10' => array('var' => 'foo', 'value' => 10),
                             'foo = 0.1' => array('var' => 'foo', 'value' => 0.1),
                             'foo = 10 == 10' => array('var' => 'foo', 'value' => 1),
                             'foo = 10 != 10' => array('var' => 'foo', 'value' => 0),
                             'foo = "foo" =~ "/[fo]+/"' => array('var' => 'foo', 'value' => 1),
                             'foo = 10 + 10' => array('var' => 'foo', 'value' => 20));
        foreach ($expressions as $expression => $object) {
            $expr = new Expression();
            echo $expression . "\n";
            $expr->evaluate($expression);
            $this->assertEquals($expr->evaluate($object['var']), $object['value']);
        }
    }
    // -------------------------------------------------------------------------
    public function tesCustomFunctions() {
        $functions = array('square(x) = x*x' => array('square(10)' => 20),
                           'number(x) = x =~ "/^[0-9]+$/"' => array(
                                'number("10")' => 1,
                                'number("10foo")' => 0
                           ),
                           'logic(x, y) = x == "foo" || x == "bar"' => array(
                                'logic("foo")' => 1,
                                'logic("bar")' => 1,
                                'logic("lorem")' => 0
                           ));
        foreach ($functions as $function => $object) {
            $expr = new Expression();
            echo $expression . "\n";
            $expr->evaluate($expression);
            foreach ($object as $fn => $value) {
                $this->assertEquals($expr->evaluate($fn), $value);
            }
        }       
    }
}

?>
