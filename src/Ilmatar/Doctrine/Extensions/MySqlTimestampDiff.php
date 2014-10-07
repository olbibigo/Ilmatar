<?php
namespace Ilmatar\Doctrine\Extensions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * Usage: TIMESTAMPDIFF(unit, expr1, expr2)
 *
 * Returns expr2 – expr1, where expr1 and expr2 are date or datetime expressions.
 * One expression may be a date and the other a datetime; a date value is treated
 * as a datetime having the time part '00:00:00' where necessary.
 * The unit for the result (an integer) is given by the unit argument, which
 * should be one of the following values: FRAC_SECOND (microseconds), SECOND,
 * MINUTE, HOUR, DAY, WEEK, MONTH, QUARTER, or YEAR.
 *
 * @author Przemek Sobstel <przemek@sobstel.org>
 */
class MySqlTimestampDiff extends FunctionNode
{
    public $firstDatetimeExpression = null;
    public $secondDatetimeExpression = null;
    public $unit = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $this->unit = $lexer->token['value'];
        $parser->match(Lexer::T_COMMA);
        $this->firstDatetimeExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondDatetimeExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sql_walker)
    {
        return sprintf(
            'TIMESTAMPDIFF(%s, %s, %s)',
            $this->unit,
            $this->firstDatetimeExpression->dispatch($sql_walker),
            $this->secondDatetimeExpression->dispatch($sql_walker)
        );
    }
}
