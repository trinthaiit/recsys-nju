select (keyword_string, uri) from database
#could be done using mysql_array()
for keyword_string, url in keywordstr_uri_mapping:
    #deal with generation 1 first
    generation1 = expand_dimension(keyword_array(keyword_string))
    for keyword_set in generation1:
        if kwset_occurrence_mapping[keyword_set] not exist:
            kwset_occur = occurrence(???)
            kwset_occurrence_mapping[keyword_set] = kw_occur
        #to use jaccard index, do trick here
        current_generation[keyword_set] = array(keyword_set, keyword_set)
    
    #now can start aggregation process
    while current_generation.size > 0:
        #the variable name is so bad!!
        for keyword_set, child_set: 
            if kwset_occurrence_mapping[keyword_set] not exist:
                kwset_occur = occurrence(???)
                kwset_occurrence_mapping[keyword_set] = kw_occur
            #calculate ratio here, exciting!
            intersection = kwset_occurrence_mapping[keyword_set]
            #not so robust here!
            union = kwset_occurrence_mapping[child_set[0]] + kwset_occurrence_mapping[child_set[1]] - intersection
            ratio = float(intersection) / union
            if ration >= THRESHOLD:
                add keyword_set, uri to database
                candidates[] = keyword_set
        current_generation = candidates

