import fs from 'node:fs';
import path from 'node:path';

const root=process.cwd();
const backup=path.join(root,'.radius-fix-backup-20261012');
const files=fs.readdirSync('wp-content/mu-plugins').filter(name=>name.endsWith('.php')).map(name=>'wp-content/mu-plugins/'+name).concat('wp-content/themes/hello-elementor/assets/css/juhani-mobile-fixes.css');
const forced=/(?<![\w-])(?:-webkit-|-moz-)?border(?:-(?:top|bottom)-(?:left|right))?-radius\s*:\s*[^;{}]*!\s*important\s*;?/gi;
let total=0;
for(const relative of files){
  const target=path.resolve(root,relative);
  if(!target.startsWith(root+path.sep))throw new Error('Path left workspace');
  const original=fs.readFileSync(target,'utf8');
  const matches=original.match(forced)||[];
  if(!matches.length)continue;
  const archived=path.join(backup,relative+'.bak');
  fs.mkdirSync(path.dirname(archived),{recursive:true});
  if(!fs.existsSync(archived))fs.writeFileSync(archived,original);
  const updated=original.replace(forced,'').replace(/^[\t ]+(?=\r?$)/gm,'');
  fs.writeFileSync(target,updated);
  total+=matches.length;
  console.log(relative+': removed '+matches.length+' forced radius declarations');
}
console.log('Total removed: '+total);
