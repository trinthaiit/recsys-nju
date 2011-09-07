set @id = 14485;

select pfr.product_id, f.name, fo.name
from bagsok.product_featureoption_relations pfr, bagsok.featureoptions fo, bagsok.features f
where product_id = @id and pfr.featureoption_id = fo.id and pfr.feature_id = f.id;