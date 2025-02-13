<?php
/**
 * @author Bernardo Fuentes Ch.
 * @since 12/05/25
 */

class itivos_qrcode_codes extends Model
{
	public $id;
	public $isin;
	public $created_date;
	public $created_by;
	public $name;
	public $link;
	public $path_code;
	public $status_db;
	
	public function __construct($id = null)
    {
        if ($id != null) {
        	if (is_int($id)) {
            	$data = $this->select($id);
        	}else {
            	$data = $this->getByISIN($id);
        	}
            $this->loadPropertyValues($data);
        }
    }
    public function getByISIN($isin)
    {
        $class = strtolower(get_class($this));
        $table = $class;
        $table = str_replace("_models", "", $table);
        $query = "SELECT * FROM ".__DB_PREFIX__.$table." WHERE isin = '".$isin."' ";
        return connect::execute($query, "select", true);
    }
	public static function getlist($page, $order_by, $sort, $show_per_page, $search)
    {
        $id_lang = 1;
        if ($sort == null) {
            $sort = "ASC";
        }
        if ($sort != "DESC" && $sort != "ASC") {
            $sort = "ASC";
        }
        if ($show_per_page == null) {
            $show_per_page = 10;
        }
        $select = '
			c.id,
			upper(c.name),
			c.created_date,
			upper(c.created_by),
			count(h.id) as views
		';
        $from = "itivos_qrcode_codes c";
        $join  = " LEFT JOIN ".__DB_PREFIX__."itivos_qrcode_statistics h ON h.id_qr = c.id ";
       
        $where = '
            c.status_db != "deleted"
        ';

        $group_by = '
            c.id,
			upper(c.name),
			c.created_date,
			upper(c.created_by)
        ';

        $result = self::paginateTable(
            $from,
            $select,
            $order_by,
            $sort,
            $page,
            $show_per_page,
            $search,
            ['c.firstname','c.lastname', 'c.email','c.status', 'i.folio'],
            $join,
            $where,
            $group_by,
        );
        return $result;;
    }
}
class_alias("itivos_qrcode_codes", "itivosQrcodeCodes");