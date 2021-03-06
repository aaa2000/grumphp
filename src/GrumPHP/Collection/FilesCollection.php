<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Finder\Comparator;
use Symfony\Component\Finder\Iterator;

/**
 * Class FileSequence
 *
 * @package GrumPHP\Collection
 */
class FilesCollection extends ArrayCollection
{
    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $collection->name('*.php')
     * $collection->name('/\.php$/') // same as above
     * $collection->name('test.php')
     *
     * @param string $pattern A pattern (a regexp, a glob, or a string)
     *
     * @return FilesCollection
     */
    public function name($pattern)
    {
        $filter = new Iterator\FilenameFilterIterator($this->getIterator(), array($pattern), array());

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $collection->name('*.php')
     * $collection->name('/\.php$/') // same as above
     * $collection->name('test.php')
     *
     * @param string $pattern A pattern (a regexp, a glob, or a string)
     *
     * @return FilesCollection
     */
    public function notName($pattern)
    {
        $filter = new Iterator\FilenameFilterIterator($this->getIterator(), array(), array($pattern));

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * Filter by path
     *
     * $collection->path('/^spec\/')
     *
     * @param $pattern
     *
     * @return FilesCollection
     */
    public function path($pattern)
    {
        $filter = new Iterator\PathFilterIterator($this->getIterator(), array($pattern), array());

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $collection->notPath('/^spec\/')
     *
     * @param $pattern
     *
     * @return FilesCollection
     */
    public function notPath($pattern)
    {
        $filter = new Iterator\PathFilterIterator($this->getIterator(), array(), array($pattern));

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * Adds tests for file sizes.
     *
     * $collection->filterBySize('> 10K');
     * $collection->filterBySize('<= 1Ki');
     * $collection->filterBySize(4);
     *
     * @param string $size A size range string
     *
     * @return FilesCollection
     *
     * @see NumberComparator
     */
    public function size($size)
    {
        $comparator = new Comparator\NumberComparator($size);
        $filter = new Iterator\SizeRangeFilterIterator($this->getIterator(), array($comparator));

        return new FilesCollection(iterator_to_array($filter));
    }

    /**
     * Adds tests for file dates (last modified).
     *
     * The date must be something that strtotime() is able to parse:
     *
     * $collection->filterByDate('since yesterday');
     * $collection->filterByDate('until 2 days ago');
     * $collection->filterByDate('> now - 2 hours');
     * $collection->filterByDate('>= 2005-10-15');
     *
     * @param string $date A date to test
     *
     * @return FilesCollection
     *
     * @see DateComparator
     */
    public function date($date)
    {
        $comparator = new Comparator\DateComparator($date);
        $filter = new Iterator\DateRangeFilterIterator($this->getIterator(), array($comparator));

        return new FilesCollection(iterator_to_array($filter));
    }
}
