import urllib.request, urllib.parse, urllib.error, http.cookiejar, re
base='http://localhost/fund_tracker/'
def client(): return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
def token(html): return re.search(r'name="csrf_token" value="([a-f0-9]+)"',html).group(1)
def get(c,path):
 r=c.open(base+path);return r,r.read().decode()
def post(c,path,data):
 return c.open(base+path,urllib.parse.urlencode(data).encode())
for name in ['gabo','niko']:
 c=client();r,html=get(c,'login.php');csrf=token(html)
 r=post(c,'login.php',{'csrf_token':csrf,'username':name,'password':'wrong-password'})
 assert 'Invalid username or password' in r.read().decode()
 r=post(c,'login.php',{'csrf_token':csrf,'username':name,'password':'admin-pass'})
 html=r.read().decode();assert r.url.endswith('/index.php') and 'Sign out' in html
 for page in ['index.php','views/index.php','views/analytics.php','views/create.php']:
  r,body=get(c,page);assert 'Fatal error' not in body and 'Warning:' not in body
 try: post(c,'views/store.php',{'name':'test','category':'deposit','amount':'1','csrf_token':'bad'})
 except urllib.error.HTTPError as e: assert e.code==403
 else: raise AssertionError('CSRF not blocked')
 try: get(c,'views/destroy.php?id=1')
 except urllib.error.HTTPError as e: assert e.code==405
 else: raise AssertionError('GET deletion allowed')
 r=post(c,'logout.php',{'csrf_token':token(html)});assert r.url.endswith('/login.php')
 r,body=get(c,'views/analytics.php');assert r.url.endswith('/login.php')
 print(name+': login, invalid password, protected pages, CSRF rejection, GET-delete rejection, logout passed')
c=client()
for page in ['index.php','views/index.php','views/create.php','views/store.php','views/destroy.php','views/analytics.php']:
 r,_=get(c,page);assert r.url.endswith('/login.php')
print('All protected routes redirect anonymous requests to login')
