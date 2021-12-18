import json
import os
import requests


def config():
    with open(os.path.dirname(os.path.abspath(__file__)) + "/config.json") as config_file:
        data = config_file.read()
    return json.loads(data)


def request(api):
    ynote_sess = config().get('YNOTE_SESS', '')
    ynote_login = config().get('YNOTE_LOGIN', '')

    if ynote_login == '' or ynote_sess == '':
        raise Exception('请先在网页端登录并复制对应Cookie值保存到config.json中，详情见 README ')

    http_header = {
        'Cookie': 'YNOTE_SESS={YNOTE_SESS}; YNOTE_LOGIN={YNOTE_LOGIN}'.format(
            YNOTE_SESS=ynote_sess,
            YNOTE_LOGIN=ynote_login
        )
    }
    return requests.get(url=api, headers=http_header)


def list_entire_by_parent_path(base_dir):
    url = "https://note.youdao.com/yws/api/personal/file?method=listEntireByParentPath" \
          "&_system=macos&sev=j1&path=/&dirOnly=true&=true"

    resp = request(url)

    ret = []
    if resp.status_code == 200:
        text = json.loads(resp.text)
        for _ in text:
            ret.append({
                "id": _['fileEntry']['id'],
                "name": _['fileEntry']['name'],
                "dir": _['fileEntry']['dir'],
                "basedir": base_dir,
            })
    return ret


def list_page_by_parent_id(parent_id, base_dir):
    url = "https://note.youdao.com/yws/api/personal/file/{id}?all=true&f=true&len=300&sort=1" \
          "&isReverse=false&method=listPageByParentId&_system=macos&sev=j1".format(id=parent_id)
    resp = request(url)
    ret = []
    if resp.status_code == 200:
        text = json.loads(resp.text)
        for _ in text['entries']:
            tmp = {
                "id": _['fileEntry']['id'],
                "name": _['fileEntry']['name'],
                "dir": _['fileEntry']['dir'],
                "basedir": base_dir,
            }
            if tmp.get("dir", False):
                ret += list_page_by_parent_id(tmp.get('id'), base_dir + tmp.get('name') + '/')
            else:
                ret.append(tmp)
    return ret


def download(file_id):
    url = "https://note.youdao.com/yws/api/personal/sync?method=download&_system=macos&sev=j1" \
          "&fileId={id}&version=-1&read=true".format(id=file_id)
    resp = request(url)
    if resp.status_code == 200:
        ret = resp.text
    else:
        ret = ''
    return ret


if __name__ == '__main__':
    basedir = os.path.dirname(os.path.abspath(__file__)) + '/note/'
    if not os.path.exists(basedir):
        os.makedirs(basedir, 0o755)
    try:
        parents = list_entire_by_parent_path(basedir)
        docs = []
        for _ in parents:
            parent_dir = _['basedir'] + _['name'] + '/'
            tmp_docs = list_page_by_parent_id(_['id'], parent_dir)
            docs += tmp_docs
            print('%s: %d' % (parent_dir, len(tmp_docs)))

        for _ in docs:
            doc_dir = _['basedir']
            if not os.path.exists(doc_dir):
                os.makedirs(doc_dir, 0o755)

            filename = doc_dir + _['name']
            print(filename)
            content = download(_['id'])

            with open(filename, 'w') as f:
                f.write(content)

        print('Over !')
    except Exception as err:
        print(err)
