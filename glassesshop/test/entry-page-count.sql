select pagetype, count(*) c
from (
    select v.pagetype pagetype
    from `glassesshop`.`visit` v, (
        select id from (
            select id, min(`time`)
            from `glassesshop`.`visit`
            group by userid
        ) t
    ) tt
    where v.id = tt.id
) ttt
group by pagetype
order by c desc;

/*
result:
home, 14866
category, 6974
product, 3891
null, 64
999, 1
*/