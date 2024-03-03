# Introduction

## What are Parsers?

Parsers are an extendable suite of classes meant to assert, convert, sanitize and extract data from a provided dataset.

You can see a list of all parsers in the system here: [List of Parsers](./list-of-parsers.md)

## Writing Parsers

- [Tutorial: Writing parsers using Base\Parser as basis](./tutorial-custom-parser-simple.md)
- [What the ResultBuilder does](./result-builder.md)
- [What is a Subject](./subject.md)

There are also a few fundamental design rules:
- The action of executing a parser _should not_ alter the internal state of the parser. The behaviour of a parser instance _should not_ change, no matter how often that parser is executed. If an internal state is needed, please tie it to the `$subject->getRoot()`.
- Parsers are configured using fluent methods, whose naming follows this pattern:
  - **Sequence control methods** are methods that define the order in which to do things in the parser. Any call to these methods must add another thing to do in the parsers internal list of commands. This can be achieved by adding the "things to do" to an array and looping through that array when executing the parser
    - `assert*`: Configures a rule that is asserted

      **Example**: `AssertInteger->assertMinimum` asserting the value to be greater or equal to the provided value 
    - `give*`: Hand an information to another parser but ignore its result on success, treading the value as immutable.
     
      **Example**: `AssertArray->giveValue` provides the value of an array key to the other parser, but even if that parser changes that value, the original value in the parsed array does not change
    - `modify*`: Hands the specified information to another parser and changes the value based on the parsers result.
      
      Example: Such as `ParseArray->modifyValue`, which provides the value of an array key to another parser and changes the value of that key to the parsers result in its result.
  - **Configuration methods** are methods that change configuration of the parser:
    - `set*`: Sets a configuration of the parser. Every new call to the same `set*` method overwrites and sometimes set methods overwrite each other (such as the `AssertInfinite` parser allowing to `setAssertPositive` and `setAssertNegative`)
  - **Chaining methods** are methods that cause other parsers/events to happen after the current parser succeeded. Any such chain always breaks at the first parser to result in an error.
    - `then*`: Chains the thing to be executed after the current parser, acting on the current parsers result.
