# List of Parsers

Here you can find a short overview of all the parsers provided with this package.

## Assert
- `Philiagus\Parser\Parser\Assert\AssertArray` for `array`

    Asserts that the provided value is an array and offers further methods to
    better assert the contents of the array
- `Philiagus\Parser\Parser\Assert\AssertBoolean` for `bool`

    Asserts that the provided value is a boolean value, so `true` or `false`
- `Philiagus\Parser\Parser\Assert\AssertEqual` for `mixed`

    Used to assert for equality. This can be equality to a predefined value or that all
    values that reach this parser are the same as the first provided value
- `Philiagus\Parser\Parser\Assert\AssertFloat` for `float`

    Asserts the value to be a float. This explicitly excludes NAN, INF and -INF
    You can define further assertions on the float value (such as min and max)
- `Philiagus\Parser\Parser\Assert\AssertInfinite` for `INF|-INF`

    Asserts that the provided value is INF or -INF. You can limit it to either by
    using the corresponding setters.
- `Philiagus\Parser\Parser\Assert\AssertInteger` for `int`

    Asserts that the provided value is an integer and also allows to assert for further
    data in the nature of the integer (such as min/max)
- `Philiagus\Parser\Parser\Assert\AssertJSONString` for `string`

    Parser used to assert that a provided value is a string containing a valid JSON.
    If you need to also extract information from the JSON string please use the ParseJSONString Parser instead.
- `Philiagus\Parser\Parser\Assert\AssertNan` for `NAN`

    Asserts that the provided value is NAN
- `Philiagus\Parser\Parser\Assert\AssertNull` for `null`

    Asserts the value to be `null`
- `Philiagus\Parser\Parser\Assert\AssertNumber` for `int|float`

    Asserts the provided value to be a number (integer or float). This can be
    limited further by using the public methods.
- `Philiagus\Parser\Parser\Assert\AssertObject` for `object`

    Asserts the value to be an object
- `Philiagus\Parser\Parser\Assert\AssertSame` for `mixed`

    Can be used to assert that the value is the same (===) as a provided value or is always the same value
    throughout runs
- `Philiagus\Parser\Parser\Assert\AssertScalar` for `scalar`

    Asserts that the received value is scalar
- `Philiagus\Parser\Parser\Assert\AssertStdClass` for `\stdClass`

    Asserts that the value is an \stdClass and allows to examine the contents of the \stdClass
- `Philiagus\Parser\Parser\Assert\AssertString` for `string`

    Parser used to assert that a value is a string. This parser treats the value as a normal PHP string,
    ignoring the encoding of the string and not trying to identify it.
    If you need to respect the encoding of the string (such as when dealing with multibyte character sequences
    as used in for example UTF-8), please use the AssertStringMultibyte parser
- `Philiagus\Parser\Parser\Assert\AssertStringMultibyte` for `string`

    Parser used to assert that a value is a string in a certain encoding
- `Philiagus\Parser\Parser\Assert\AssertStringRegex` for `string`

    Parser used to assert a value as string and using a regex to check that string

## Convert
- `Philiagus\Parser\Parser\Convert\ConvertToArray` for `mixed -> array`

    Tries to convert the received value to an array if it is not already.
    
    You can define the type of conversion by using the different static constructors.
- `Philiagus\Parser\Parser\Convert\ConvertToDateTime` for `string|\DateTimeInterface -> \DateTime|\DateTimeInterface`

    Converts the received value to a \DateTime or \DateTimeImmutable if it isn't already
- `Philiagus\Parser\Parser\Convert\ConvertToEnum` for `mixed -> \UnitEnum`

    Parser used to convert a value to an element of a PHP enum
    Matching can be performed by enum name, backed value or both
- `Philiagus\Parser\Parser\Convert\ConvertToInteger` for `mixed -> integer`

    Takes any input and attempts a loss free conversion of the provided value into a valid integer value
    
    Conversion is attempted for floats and strings
- `Philiagus\Parser\Parser\Convert\ConvertToString` for `mixed -> string`

    Tries to convert the provided value to a string. Please be aware that you
    must define the specific way of conversion for certain types (like how arrays are imploded or
    what true/false are supposed to become)

## Extract
- `Philiagus\Parser\Parser\Extract\Append` for `mixed`

    Whenever this parser is called the value received by this parser is appended to the provided target
    
    If the provided target is not an array at that point, Append will convert `null` to an empty array
    
    Based on PHP reference rules this parser takes some type possession of the provided target
    
    The target can only be `null|array|\ArrayAccess`, where `null` will be internally converted to an empty array on parser creation.
- `Philiagus\Parser\Parser\Extract\Assign` for `mixed`

    Stores the value into the provided variable, however the desired result
    of this parser is in most cases accomplished by chaining `thenAssignTo` to any other parser
    that implements the `Chainable`-Interface (which is most parsers).

## Generic
- `Philiagus\Parser\Parser\Callback` for `mixed`

    **Target Type**: mixed
    
    A parser that simplifies single-use cases where normally an entire parser would have been written
    
    This parser takes a closure with signature `\Closure(mixed, Subject): mixed`
    
    If this closure throws an error the parser will convert that exception to an Error and log
    it correspondingly, honoring the current parser mode (throw mode or gather mode)
    
    On no error the result of this parser is the result of the closure

## Logic
- `Philiagus\Parser\Parser\Logic\Any` for `mixed`

    A parser that matches any value without further validation
- `Philiagus\Parser\Parser\Logic\Chain` for `mixed`

    Parser used to chain multiple parsers after one another, feeding the result of the previous parser
    to the next. The chain is broken when a parsers result has errors.
- `Philiagus\Parser\Parser\Logic\Conditional` for `mixed`

    This parser allows to set match the provided value against configured values and - on match - call a
    corresponding followup parser. Think of it as the PHP switch construct in parser form.
- `Philiagus\Parser\Parser\Logic\Fail` for `mixed`

    Parser that always fails, generating an error with a defined message
    
    This parser is most times used in conjunction with other Logic parsers, such as Map
    a certain value to an automatic fail
- `Philiagus\Parser\Parser\Logic\Fork` for `mixed`

    Forks out the received subject to multiple other parsers
    The result of this parser is identical to the received value, even
    if any of the provided parsers changes the value
- `Philiagus\Parser\Parser\Logic\IgnoreInput` for `mixed`

    This parser ignores its received value and replaces it with a predefined value
- `Philiagus\Parser\Parser\Logic\OneOf` for `mixed`

    Checks that the value provided matches one of the provided values or parsers
    
    Please be aware that these values are not evaluated in order. For performance reasons the same and equal
    values are accumulated and compared before the list of parsers are checked.
- `Philiagus\Parser\Parser\Logic\OverwriteErrors` for `mixed`

    This parser catches the parsing errors generate by the child parser and overwrite the
    error with a new Error that has a provided message
    This is used to create more meaningful error messages for consumers of the parser
    This also means, that the subject of the resulting error is the subject provided
    to this OverwriteErrors parser rather than the subject that any caught error might
    originate from.
- `Philiagus\Parser\Parser\Logic\Preserve` for `mixed`

    Preserves a value around another parser, shielding it from alteration
- `Philiagus\Parser\Parser\Logic\Unique` for `mixed`

    Creates a parser that acts as a logical gate that will not let the same value through twice.
    
    This is best used in combination with an Append parser to have a unique list of elements in the
    resulting array

## Parse
- `Philiagus\Parser\Parser\Parse\ParseArray` for `array`

    Parser to not only validate an array but also alter it. This parser is an extension of the
    Assert Array parser and allows to change individual values rather than just look at them.
- `Philiagus\Parser\Parser\Parse\ParseBase64String` for `string`

    Parser used to base64 decode a string
    
    This parser uses strict decoding by default, but can be set to be non-strict using
    the setStrict method
- `Philiagus\Parser\Parser\Parse\ParseFormEncodedString` for `string`

    Parses the provided string treating it as form encoded data
    The result of the parser is the parsed data
- `Philiagus\Parser\Parser\Parse\ParseJSONString` for `string`

    Parses as string as JSON and returns the parsed result
- `Philiagus\Parser\Parser\Parse\ParseStdClass` for `\stdClass`

    Used to alter an \stdClass object. This parser will _never_ alter the object that it
    received but instead opt to _clone_ the object and apply its alterations ot the clone.
    
    This parser also extends AssertStdClass, so that you can do both assertion and alteration at the
    same time.
- `Philiagus\Parser\Parser\Parse\ParseURL` for `string`

    Parses the provided string, treating is a URL, and returns the resulting parts.
    
    While this parser is most times used for extraction (via the give* methods),
    its result value is exactly the result of the core PHP parse_url function.

