select * 
from `bagsok`.`browse`
where (uri = 'winter-love-series-shoulder-bag-1107009820' OR uri = 'cute-love-match-cross-body-bag') AND cookie_id IN (
    select cookie_id
    from `bagsok`.`browse`
    where uri = 'simple-but-elegant-love-match-bag'
);