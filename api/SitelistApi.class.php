<?php
class SitelistApi extends Api
{
	private $_allowed_fields = array('site_id', 'name', 'url', 'logo', 'description');
	
	private function _formatSite($site)
	{
		foreach ($site as $k => $v)
        	if (!in_array($k, $this->_allowed_fields))
        		unset($site[$k]);
        		
    	return $site;
	}
	
    public function getSitelist()
    {
        $res = D('Site', 'sitelist')->getApprovedSiteList($this->since_id, $this->max_id, $this->count, $this->data['content'],'status_mtime DESC', $this->page);
        foreach ($res as &$v)
        	$v = $this->_formatSite($v);
        
        return $res;
    }
    
    public function getSiteStatus()
    {
        $site   = D('Site', 'sitelist')->getSite($this->id);
        $status = null;
        $alias  = null;
        switch ($site['status']) {
            case SiteModel::SITE_STATUS_APPLIED:
                $status = 0;
                $alias  = 'APPLIED';
                break;
            case SiteModel::SITE_STATUS_APPROVED:
                $status = 1;
                $alias  = 'APPROVED';
                break;
            case SiteModel::SITE_STATUS_DENIED:
                $status = 0;
                $alias  = 'DENIED';
                break;
            default:
                $status = 0;
                $alias  = 'NOT EXIST';
        }
        
        $site = $status == 1 ? $this->_formatSite($site) : '';
        return array('status' => $status,
                     'alias'  => $alias,
                     'data'   => $site);
    }
}