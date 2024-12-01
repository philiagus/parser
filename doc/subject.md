# Subject

To execute a parser, you must provide the parser with a subject. The default subject can be created using

```php
Subject::default(mixed $value, ?string $description = null, bool $throwOnError = true)
```

The parameters are:
- `$value`: The value to be parsed
- `$description`: This optional parameter allows you to set a description of the subject. This string is used in the error path on parsing errors. This value is defaulted to the type of the value provided to the Subject
- `$throwOnError`: If true the parsers abort on the first error and a `ParsingException` is thrown (`throw mode`). If false the Parsers accumulate errors (`gather mode`) and the result of the `parse` method will contain the list of errors

The subject also provides an internal memory, shared among all subjects that derive from the root subject of the parser execution. This can be used to preserve data between parser executions within the same subject stem.

## Utility subjects

Utility subjects are used as in-between steps in the path of the parsing. There are two ways to think of the path the parsers take through the subject:

1. The location of the information within the value of the subject (so currently parsing the first element of the provided array)
2. The logical code flow within the parsers to get to that location (start of assert array → first element of the array → start of specific parser of that element → ...)

The subjects used to represent the second path are called "Utility subjects". Any subject (through its source subjects) provide both paths. Type 1 is most times reported in Exceptions and Responses ("You have made an error at that level of your provided structure/JSON/..."), while type 2 is most useful in debugging ("The parsers ran in that order and did these things to result in an error").

## Predefined subject types

The parsers come with a list of subjects to be used in the parsers. For ease of reading, this table assumes `use Philiagus\Parser\Subject;`.

| Name `use \Philiagus\Parser\Subject` | Is Utility? | Purpose                                                                                                                                                                                                                                                                                                                                                                         |
|--------------------------------------|-------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Subject\Root`                       | false       | The default subject to use on root level and the result of `Subject::default`. You _should not_ use any other subject on parser entry point                                                                                                                                                                                                                                     |
| `Subject\ArrayKey`                   | false       | An array key being parsed, _not_ the value of an array key (example: of the array `['key' => 'value']` the string `'key'`                                                                                                                                                                                                                                                       |
| `Subject\ArrayValue`                 | false       | The value of an array element being parsed (example: of the array `['key' => 'value']` the string `'value'`                                                                                                                                                                                                                                                                     |
| `Subject\ArrayKeyValuePair`          | false       | The subject will contain an array with two elements - an arrays key and its value (example: of the array `['key' => 'value', 'another key' => 'another value']` it could contain `['key', 'value']`). This directly references the way [Array.entries()](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/entries) of JavaScript behaves. |
| `Subject\MetaInformation`            | false       | Used to parse a meta information, such as the length of a string or the number of elements in an array. Its good to think of meta information as any value that is not represented in the serialization of the value.                                                                                                                                                           |
| `Subject\PropertyName`               | false       | Behaves like the `ArrayKey` but for properties of objects                                                                                                                                                                                                                                                                                                                       |
| `Subject\PropertyValue`              | false       | Behaves liek the `ArrayValue` but for values of properties of objects                                                                                                                                                                                                                                                                                                           |
| `Subject\PropertyNameValuePair`      | false       | Behaves like `ArrayKeyValuePair` but for objects properties. This directly references the way [Object.entries()](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/entries) of JavaScript behaves.                                                                                                                                        |
| `Subject\Utility\Forwarded`          | true        | Used to forward a subject unchanged to another parser, but add some utility information while doing so. Example use: In the Conditional parser the parser forwards a `Forwarded` subject with an information on which Condition matched.                                                                                                                                        |
| `Subject\Utility\Internal`           | true        | This subject is used when a parser alters the value internally and performs additional tasks afterwards. Example use: When the ParseArray parser alters the array be adding a key it creates an `Internal` subject hinting towards the structure change.                                                                                                                        |                                                                                                                                                                                                                                    |
| `Subject\Utility\ParserBegin`        | true        | Marks the beginning of a single parser, which helps debugging the location of an error by allowing human readable access to the chain of parsers executed.                                                                                                                                                                                                                      |
| `Subject\Utility\Test`               | true        | Marks the subject as a Test-Subject. This is used by some parsers if they only want to see whether a sub-parser succeeds or not and based on that condition wants to execute further code.                                                                                                                                                                                      |


