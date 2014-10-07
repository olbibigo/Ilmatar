<?php
namespace Ilmatar\Doctrine\Extensions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
* "DATE" "(" SimpleArithmeticExpression ")".
* Modified from DoctrineExtensions\Query\Mysql\Year
*
* More info:
* http://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_date
*
* @author Dawid Nowak <macdada@mmg.pl>
*/
class MySqlDate extends FunctionNode
{
    public $date;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sql_walker)
    {
        return 'DATE('.$sql_walker->walkArithmeticPrimary($this->date).')';
    }
}
