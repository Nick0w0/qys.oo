<?php

namespace app\admin\controller\discover;

class Review extends Discover
{
    public function index()
    {
        $this->relationSearch = true;
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $relations = ['user', 'discovertag', 'discovertopic'];
            if ($this->hasSchoolScopeSchema()) {
                $relations[] = 'school';
            }

            $query = $this->model->with($relations)->where($where);
            if ($this->hasModerationSchema()) {
                $query->where('discover.audit_status', 'pending');
            }
            if (!$this->auth->isSuperAdmin() && $this->hasSchoolScopeSchema()) {
                $schoolId = $this->getCurrentSchoolId();
                $query->where('discover.school_id', $schoolId ?: -1);
            }

            $list = $query->order($sort, $order)->paginate($limit);
            foreach ($list as $row) {
                if ($row->getRelation('user')) {
                    $row->getRelation('user')->visible(['nickname']);
                }
                if ($row->getRelation('discovertag')) {
                    $row->getRelation('discovertag')->visible(['name']);
                }
                if ($row->getRelation('discovertopic')) {
                    $row->getRelation('discovertopic')->visible(['name']);
                }
                if ($this->hasSchoolScopeSchema() && $row->getRelation('school')) {
                    $row->getRelation('school')->visible(['name']);
                }
            }
            return json(['total' => $list->total(), 'rows' => $list->items()]);
        }
        return $this->view->fetch();
    }
}
