<?php
namespace core\models\Db;

class CountryDao extends AbstractDao
{
    public function __construct(DbResource $db)
    {
        parent::__construct($db);
    }

    public function get_countries(): array
    {
        return $this->db->query(<<<SQL
select country_id, country_code, country_name
from countries;
SQL
        )->executeAndFetchAll();
    }
}