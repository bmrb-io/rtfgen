package EDU.bmrb.rtfgen;

import java.util.*;

/**
 * This class defines a bunch of static methods for efficiently sorting
 * Vectors.  It also defines two interfaces that
 * provide two different ways of comparing objects to be sorted.
 *
 * Adapted by Joe Crumpton, October 2000
 * from "Java Examples in a Nutshell" Sorter class
 **/
public class VectorSorter {
	/**
	 * This interface defines the compare() method used to compare two objects.
	 * To sort objects of a given type, you must provide a Comparer
	 * object with a compare() method that orders those objects as desired
	 **/
	public static interface Comparer {
	/**
	 * Compare objects, return a value that indicates their relative order:
	 * if (a > b) return > 0; 
	 * if (a == b) return 0;
	 * if (a < b) return < 0. 
	 **/
		public int compare(Object a, Object b);
	}

	/**
	 * Sort a portion of a vector, using the comparison defined by
	 * the Comparer object c.  If up is true, sort into ascending order, 
	 * otherwise sort into descending order.
	 **/
	public static void sort(Vector a, int from, int to, boolean up,
			    Comparer c)
	{
		sort(a, null, from, to, up, c);
	}
	/**
	 * This is the main sort() routine. It performs a quicksort on the elements
	 * of vector a between the element from and the element to.  The up argument
	 * specifies whether the elements should be sorted into ascending (true) or
	 * descending (false) order.  The Comparer argument c is used to perform
	 * comparisons between elements of the vector.  The elements of the vector b
	 * are reordered in exactly the same way as the elements of vector a are.
	 **/
	public static void sort(Vector a, Vector b, 
			    int from, int to, 
			    boolean up, Comparer c)
	{
		// If there is nothing to sort, return
		if ((a == null) || (a.size() < 2)) return;
	
		// This is the basic quicksort algorithm, stripped of frills that can
		// make it faster but even more confusing than it already is.  You
		// should understand what the code does, but don't have to understand
		// just why it is guaranteed to sort the vector...
		// Note the use of the compare() method of the Comparer object.
		int i = from, j = to;
		Object center = a.elementAt((from + to) / 2);
		do {
			if (up) {  // an ascending sort
				while((i < to)&& (c.compare(center, a.elementAt(i)) > 0)) i++;
				while((j > from)&& (c.compare(center, a.elementAt(j)) < 0)) j--;
			} else {   // a descending sort
				while((i < to)&& (c.compare(center, a.elementAt(i)) < 0)) i++;
				while((j > from)&& (c.compare(center, a.elementAt(j)) > 0)) j--;
			}
			if (i < j) 
		        { 
			    Object tmp = a.elementAt(i);  
			    a.setElementAt( a.elementAt(j), i );  
			    a.setElementAt( tmp, j ); // swap elements
			    if (b != null) 
			    { 
				tmp = b.elementAt(i); 
				b.setElementAt( b.elementAt(j), i ); 
				b.setElementAt( tmp, j ); 
			    } // swap
			}
			if (i <= j) { i++; j--; }
		} while(i <= j);
		if (from < j) sort(a, b, from, j, up, c); // recursively sort the rest
		if (i < to) sort(a, b, i, to, up, c);
	}
	public static void sort(Vector a, Vector b, Comparer c) {
		sort(a, b, 0, a.size()-1, true, c);
	}
	/**
	 * Sort a vector of arbitrary objects into ascending order, using the 
	 * comparison defined by the Comparer object c
	 **/
	public static void sort(Vector a, Comparer c) {
		sort(a, null, 0, a.size()-1, true, c);
	}
}
