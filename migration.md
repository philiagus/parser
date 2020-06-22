# Migration document
## v1.0.1 to v1.1.0

### AssertStringMultibyte
- `assertRegex` added

### AssertString
- `assertRegex` added

### AssertStringRegex
- added

### Fail
- added
- will always fail

### Any
- added
- will accept any value

### OneOf
- `setDefaultResult` added
    - can only be called once
    - if none of the elements match, the provided value is returned

### Map
- added

## v1.0.0 to v1.0.1

### Base\Parser
- `new`
    - correctly hints towards `static` in docblock
    - correctly hints towards `self` in return type

## v1.0.0-RC7 to v1.0.0

### General
- The replacer `{???.debug}` no longer prints an excerpt of binary strings, instead binary strings will be presented as `string<binary>(32)` where 32 is the bit length of the binary string
    - behaviour for UTF-8 and ASCII is unchanged

### AssertStringMultibyte
- if no encoding is provided detection is now down via `mb_detect_encoding($value, "auto", true)` instead of `mb_detect_encoding($value)`
    - if no encoding could be detected the class throws an exception
    - the message of the exception thrown can be overwritten using `overwriteEncodingDetectionExceptionMessage`

### Debug
- `stringify`
    - binary strings no longer print characters, instead binary strings will be presented as "string<binary>(32)" where 32 is the bit length of the binary string
    - behaviour for UTF-8 and ASCII is unchanged
    
## v1.0.0-RC6 to v1.0.0-RC7

### Contract\Parser interface

It has come to my attention that writing tests and prophecies for classes using the parsers is complicated.

To target this issue, the Parser Contract has been created and the Base Parser implements that contract. All methods that take parsers as an argument expect instances of that interface from now on.

### Base\Parser
- `then`
    - if called multiple times, instead of overwriting previously set then, now chains them to one another
    - result of the main parser is the result of the last parser in the chain
    - every parser in the chain receives the result of its previous element in the chain
    
- new method `setParsingExceptionOverwrite`
    - if set every with a string: ParsingException thrown by this Parser or Parsers called inside its `execute` or `then` will be caught and replaced by a ParsingException with the given content.
    - if `null` is provided: it blocks that any overwrite can be set


## v1.0.0-RC5 to v1.0.0-RC6

### General

A more customizable way of string preparation for exception messages was added. This unfortunately means, that some defined exception messages will no longer have the expected string result.

This change brings lots of potential for the future, as it offers a better way of selecting the level of information provided in the exception strings.

In some cases replacers such as `{type}` are now replaced with the more clear `{value.type}` and the older replacer will simply not be replaced.

**If you are already using custom exception messages or are relying on the string representation of an already existing exception message please align your code correspondingly.**

### AssertInteger
- `withMultipleOf`
    - renamed from `isMultipleOf`

### New Class \Philiagus\Parser\Util\Debug

The class `\Philiagus\Parser\Util\Debug` was added. As the namespace suggests it is a utility class, not a parser. The class is static.

- `getType($value): string`
    - Returns a string representation of the type of the provided variable
    - NAN, INF and -INF are represented as corresponding strings `NAN`, `INF` and `-INF`
    - objects are represented as `object<className>`
    - float are represented as `float`
    - all other values will simply return whatever gettype returns
- `stringify($value): string`
    - creates a string representation of the value
    - `integer` will be `integer 1234`
    - not-special `float` will be `float 3214.15`
    - `NAN` will be `NAN`
    - `INF` will be `INF`
    - `-INF` will be `-INF`
    - `boolean` are `boolean TRUE` or `boolean FALSE` respectively
    - `string` will be represented as `string<encoding>(length)"characters"`
        - encoding can be `ASCII`, `UTF8` or `binary`
        - length is the amount of characters in bytes, _not_ the length of the string in the encoding
        - `characters` contains the first up to 32 characters, or 31 followed by ellipsis
        - control characters are replaced by a corresponding visual placeholder
    - arrays are displayed as `array<keyType,valueType>(length)`
        - `keyType` can be `integer`, `string` or `mixed`
        - `valueType` can be any type provided by `Debug::getType`, as long as the are all the same. If not, `valueType` will be `mixed`
        - `length` is the number of elements in the array
    - all other types are simply returned as `Debug::getType` representation
- `parseMessage(string $message, array $replacers): string`
    - Uses $message as a string and $replacers as an array of elements to be replaced into it
    - The replacement elements look like this `{arrayKey}`"`, performing a raw replacement, or `{arrayKey.infoType}`, transforming the value before replacing.
        - `infoType` can be one of the following:
            - `gettype`: The result of a call to gettype on the replacers element
            - `type`: the same as gettype for anything but objects. For objects its "object<className>"
            - `debug`: a string representation of the value, tying to show as much of the content as possible, see Debug::stringify
            - `export`: the result of var_export of the value
            - `raw`: the raw value form the replacers
    - Only valid replacers are replaced. If the key or the infoType is not known that replacer won't be replaced.

### Internal
- Namespace of test cases has been renamed and moved

## v1.0.0-RC4 to v1.0.0-RC5
### All Parsers

A naming convention was added to the system:
- `withXYZ` or `addXYZ`
    - adds a configuration or a parser without overwriting already existing configuration of the same type. Example: calling `withLength` twice will not cause the second defined length to overwrite the previous one. Instead both definitions will be kept and the parser will execute both
    - All configuration added with `with` methods will be executed in order of configuration
- `setXYZ`
    - sets specific configurations that can only be set once and will not be overwritten
    - Configuration defined with `set` will be executed at a defined time outside the order of `with` configuration as they are most times crucial for the system to work correctly
- `overwriteXYZ`
    - sets a specific configuration, silently overwriting any previously set configuration for the same field

### AssertArray
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`

### AssertBoolean
- `withExceptionMessage`
    - renamed to `overwriteExceptionMessage`

### AssertEquals
- `withValue`
    - renamed to `setValue`
    - can only be called once
- static `with`
    - renamed to `value`

### AssertFloat
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`
- `withMinimum`
    - no longer checks the provided value to be valid against an already set maximum, so no `ParserConfigruationException` is thrown anymore
    - adds another minimum instead of overwriting the previously set minimum
- `withMaximum`
    - no longer checks the provided value to be valid against an already set minimum, so no `ParserConfigruationException` is thrown anymore
    - adds another maximum indestad over overwriting the previously set maximum

### AssertInfinite
- `withExceptionMessage`
    - renamed to `overwriteExceptionMessage`

### AssertInteger
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`
- `withMinimum`
    - no longer checks the provided value to be valid against an already set maximum, so no `ParserConfigruationException` is thrown anymore
    - adds another minimum instead of overwriting the previously set minimum
- `withMaximum`
    - no longer checks the provided value to be valid against an already set minimum, so no `ParserConfigruationException` is thrown anymore
    - adds another maximum indestad over overwriting the previously set maximum

### AssertNan
- `withExceptionMessage`
    - renamed to `overwriteExceptionMessage`

### AssertNull
- `withExceptionMessage`
    - renamed to `overwriteExceptionMessage`

### AssertNummeric
- renamed to `AssertNumber`
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`
- `withMinimum`
    - no longer checks the provided value to be valid against an already set maximum, so no `ParserConfigruationException` is thrown anymore
    - adds another minimum instead of overwriting the previously set minimum
- `withMaximum`
    - no longer checks the provided value to be valid against an already set minimum, so no `ParserConfigruationException` is thrown anymore
    - adds another maximum indestad over overwriting the previously set maximum

### AssertSame
- `withValue`
    - renamed to `setValue`
    - can only be called once
- static `with`
    - renamed to `value`

### AssertScalar
- `withExceptionMessage`
    - renamed to `overwriteExceptionMessage`

### AssertStdClass
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`
- `withProperty`
    - expects first parameter to be a string

### AssertString
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`
- `withLength`
    - no longer overwrites previously set withLength, instead adds a new parser to validate the length against

### AssertStringMultibyte
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`
- `withLength`
  - no longer overwrites previously set withLength, instead adds a new parser to validate the length against
- `withEncoding` renamed to `setEncoding`
    - This method will be executed before all `with` methods
    - The defined encoding is used as the encoding for all followup parsing (such as substring cutting)
    - If the provided string does not match this encoding a `ParsingException` is thrown


### ConvertFromJson
- `withConversionExceptionMessage`
    - renamed to `overwriteConversionExceptionMessage`
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`
- `withObjectsAsArrays`
    - renamed to `setObjectsAsArrays`
    - added a boolean parameter, default `true`
    - throws `ParserConfigurationException` if called twice
- `withMaxDepth`
    - renamed to `setMaxDepth`
    - throws `ParserConfigurationException` if called twice
- `withBigintAsString`
    - renamed to `setBigintAsString`
    - throws `ParserConfigurationException` if called twice
    - added a boolean parameter, default `true`

### ConvertToArray
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`
- `convertNonArraysWithArrayCast`
    - removed in favor of `setConvertNonArrays`
- `convertNonArraysWithKey`
    - removed in favor of `setConvertNonArrays`
- `setConvertNonArrays`
    - added
    - first parameter is one of these constants:
        - ConvertToArray::CONVERSION_DO_NOT_CONVERT
            - no conversion will be done and a type exception is thrown
        - ConvertToArray::CONVERSION_ARRAY_CAST
            - a simple (array) cast will be performed
        - ConvertToArray::CONVERSION_ARRAY_WITH_KEY
            - an array will be created with key being the second parameter of this method

### ConvertToInteger
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`

### ConvertToString
- `withTypeExceptionMessage`
    - renamed to `overwriteTypeExceptionMessage`
- `withBooleanValues`
    - renamed to `setBooleanValues`
    - throws `ParserConfigurationException` if called twice
- `withImplodeOfArrays`
    - renamed to `setImplodeOfArrays`
    - throws `ParserConfigurationException` if called twice

### Fixed
- static `value`
    - added
- `withValue`
    - renamed to `setValue`
    - throws `ParserConfigurationException` if called twice

### OneOf
- throws `OneOfParsingException` (extends `MultipleParsingException`) instead of `MultipleParsingException`
- default exception message change from `Provided value does not match any of the expected formats` to `Provided value does not match any of the expected formats or values`
- no longer throws a `ParserConfigurationException` if not options are provided
- `withNonOfExceptionMessage`
    - renamed to `overwriteNonOfExceptionMessage`
- `addOption`
    - is now a variadic function
- `addSameOption`
    - is now a variadic function
    - no longer adds a `AssertSame` internally, instead uses internal methods to assert identity (high performance improvement). This does not change the behaviour of this method
- `addEqualsOption`
    - is now a variadic function
    - no longer adds a `AssertSame` internally, instead uses internal methods to assert equality (high performance improvement). This does not change the behaviour of this method

### OneOfParsingException
- added, extends `MultipleParsingException`
- differences to `MultipleParsingException`
    - `getParsingExceptions`
        - no longer contains exception for `addSameOption` and `addEqualsOption` options
    - `getSameOptions`
        - added
        - lists the options added to `OneOf` Parser via `addSameOption`
    - `getEqualsOptions`
        - added
        - lists the options added to `OneOf` Parser via `addEqualsOption`