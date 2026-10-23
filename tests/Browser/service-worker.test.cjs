const {test}=require('node:test');
const assert=require('node:assert/strict');
const vm=require('node:vm');
const fs=require('node:fs');
const source=fs.readFileSync('public/sw.js','utf8');
function worker(overrides={}){
 const handlers={};const environment={URL,Response,Promise,
 self:{location:{origin:'https://familyplan.test'},registration:{navigationPreload:{disable:async()=>{}}},clients:{claim:async()=>{}},skipWaiting:async()=>{},addEventListener:(type,fn)=>handlers[type]=fn},
 caches:{open:async()=>({match:async()=>null,put:async()=>{},add:async()=>{}}),keys:async()=>[]},fetch:async()=>new Response('network'),...overrides};
 vm.runInNewContext(source,environment);return handlers;
}
function request(handlers,path='/',preload=Promise.resolve(undefined)){
 let result;handlers.fetch({request:{method:'GET',mode:path==='/'?'navigate':'cors',url:'https://familyplan.test'+path},preloadResponse:preload,respondWith:p=>result=p});return result;
}
test('a rejected navigation preload retries the network',async()=>{
 assert.equal(await(await request(worker(), '/',Promise.reject(new Error('preload failed')))).text(),'network');
});
test('an offline launch returns a private-data-free retry page, then recovers online',async()=>{
 let online=false;const handlers=worker({fetch:async()=>{if(!online)throw new Error('offline');return new Response('online');}});
 const response=await request(handlers);assert.equal(response.status,503);assert.equal(response.headers.get('Cache-Control'),'no-store');assert.match(await response.text(),/Riprova/);
 online=true;assert.equal(await(await request(handlers)).text(),'online');
});
test('cache open, read and quota failures do not break static asset requests',async()=>{
 for(const open of [async()=>{throw new Error('storage denied');},async()=>({match:async()=>{throw new Error('cache read');},put:async()=>{throw new Error('quota');}})]){
  const handlers=worker({caches:{open}});assert.equal(await(await request(handlers,'/js/app.js')).text(),'network');
 }
});
test('install and activation survive unavailable cache storage',async()=>{
 const handlers=worker({caches:{open:async()=>{throw new Error('storage');},keys:async()=>{throw new Error('storage');}}});
 for(const stage of ['install','activate']){let done;handlers[stage]({waitUntil:p=>done=p});await done;}
});
test('authenticated pages and mutations are never stored as static assets',()=>{
 const handlers=worker();let intercepted=false;for(const [method,path]of [['GET','/passwords'],['POST','/login']])handlers.fetch({request:{method,mode:'cors',url:'https://familyplan.test'+path},respondWith:()=>intercepted=true});assert.equal(intercepted,false);
});
