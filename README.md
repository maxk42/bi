BRAINFUCK INTERPRETER
=====================

This is a Brainfuck interpreter which also has support for numeric arguments.
For instance, to add ten to the current cell on the data strip, one may write
'10+' instead of '++++++++++'.  It is fully backward-compatible with standard
Brainfuck programs.

Anything which is not a valid Brainfuck instruction or numeric prefix will be
treated as a comment.  See 'hello.bf' for examples.


USAGE
-----
From the command line on a *nix system, enter the directory where you've installed the brainfuck interpreter and type:

    ./bf-interpret hello.bf

Which should display the following output:

    Hello, World!

If you wish to incorporate your brainfuck interpreter into other projects, the file bi.php contains a standalone
class interpreter class.  See that file for further instructions.


FILES
-----

* bi.php - Contains the base interpreter.  May be used as a standalone interpreter that can be included in other programs.
* bi-interpret - Executable PHP (on *nix systems) script.  Invoke with ./bi-interprter <target script>
* hello.bf - A "hello world" app that makes liberal use of the interpreter's custom features.
* fact.bf - An example application that accepts an integer input and prints its factors.


