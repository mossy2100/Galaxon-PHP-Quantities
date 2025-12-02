We want to prioritise integer operations.

1. Two integers (or negation of an integer)
2.

After that, we want to prioritise:
1. Additions, subtractions, negations (equal)

There are two things going on here.
1. The accumulated error score of a number.
2. The preference for an operation.

They are related though. We want to produce numbers with low error scores.

So let's score each operation.

We shouldn't increase the error score if an operation produces an integer.
