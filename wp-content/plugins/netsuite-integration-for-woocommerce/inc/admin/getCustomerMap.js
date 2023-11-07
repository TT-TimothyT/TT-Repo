/**
 * @NApiVersion 2.x
 * @NScriptType MapReduceScript
 * @NModuleScope SameAccount
 */
define(['N/error', 
    'N/record', 
    'N/runtime',
    'N/search'],
/**
 * @param {email} email
 * @param {error} error
 * @param {record} record
 * @param {runtime} runtime
 * @param {search} search
 */
function(error, record, runtime, search) 
{

	/**
	 * Map/Reduce Script:
	 * Sample Map/Reduce script for blog post.  
	 */
	
	
    /**
     * Marks the beginning of the Map/Reduce process and generates input data.
     *
     * @typedef {Object} ObjectRef
     * @property {number} id - Internal ID of the record instance
     * @property {string} type - Record type id
     *
     * @return {Array|Object|Search|RecordRef} inputSummary
     * @since 2015.1
     */
    function getInputData() 
    {   
    	//Dynamically create Saved Search to grab all eligible Sales orders to invoice
    	//In this example, we are grabbing all main level data where sales order status are 
    	//any of Pending Billing or Pending Billing/Partially Fulfilled
    	search.create({
    		'type':search.Type.CUSTOMER,
    		'filters':[
              [name: 'email', operator: search.Operator.ISNOTEMPTY],
              ],
    		'columns':[
              'internalid',
              'email',
              ]
    	});

        var customerSearch = search.create(customer_search_object);
        var resultSet = customerSearch.run();
        // declare array for results
        var currentRange = resultSet.getRange({
            start : 0,
            end : 1000
        });
        var requestObj = [];
        var itemDetailsObj = {};  
    var i = 0;  // iterator for all search results
    var j = 0;  // iterator for current result range 0..999
    while ( j < currentRange.length ) {
        var result = currentRange[j];
        requestObj[k].id = result.getValue('internalid');

        i++; j++;
        if( j==1000 ) {   // check if it reaches 1000
          j=0;          // reset j an reload the next portion
          currentRange = resultSet.getRange({
            start : i,
            end : i+1000
        });
      }
  } 

  return JSON.stringify(requestObj);  
}

    /**
     * Executes when the map entry point is triggered and applies to each key/value pair.
     *
     * @param {MapSummary} context - Data collection containing the key/value pairs to process through the map stage
     * @since 2015.1
     */
function map(context) 
{


}

    /**
     * Executes when the summarize entry point is triggered and applies to the result set.
     *
     * @param {Summary} summary - Holds statistics regarding the execution of a map/reduce script
     * @since 2015.1
     */
function summarize(summary) 
{


}

return {
    getInputData: getInputData,
       // map: map,
       // summarize: summarize
};

});