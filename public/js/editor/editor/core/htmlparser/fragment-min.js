KISSY.Editor.add("htmlparser-fragment",function(){function n(){this.children=[];this.parent=z;this._={isBlockLike:q,hasInlineStarted:v}}var q=true,v=false,z=null,j=KISSY.Editor,F={colgroup:1,dd:1,dt:1,li:1,option:1,p:1,td:1,tfoot:1,th:1,thead:1,tr:1},r=KISSY,w=j.Utils,y=j.NODE,g=j.XHTML_DTD;w.mix({table:1,ul:1,ol:1,dl:1},g.table,g.ul,g.ol,g.dl);var D=g.$list,G=g.$listItem;n.FromHtml=function(i,e){function o(b){var f;if(k.length>0)for(var h=0;h<k.length;h++){var d=k[h],c=d.name,l=g[c],s=a.name&&g[a.name];
if((!s||s[c])&&(!b||!l||l[b]||!g[b])){if(!f){m();f=1}d=d.clone();d.parent=a;a=d;k.splice(h,1);h--}}}function m(){for(;A.length;)a.add(A.shift())}function t(b,f,h){f=f||a||B;if(e&&!f.type){var d,c;if((d=b.attributes&&(c=b.attributes._ke_real_element_type)?c:b.name)&&!(d in g.$body)&&!(d in g.$nonBodyContent)){c=a;a=f;p.onTagOpen({li:"ul",dt:"dl",dd:"dl"}[d]||e,{});f=a;if(h)a=c}}if(b._.isBlockLike&&b.name!="pre"){h=b.children.length;if((d=b.children[h-1])&&d.type==y.NODE_TEXT)if(c=w.rtrim(d.value))d.value=
c;else b.children.length=h-1}f.add(b);if(b.returnPoint){a=b.returnPoint;delete b.returnPoint}}var p=new j.HtmlParser,B=new n,k=[],A=[],a=B,x=v,C;p.onTagOpen=function(b,f,h){var d=new j.HtmlParser.Element(b,f);if(d.isUnknown&&h)d.isEmpty=q;if(g.$removeEmpty[b])k.push(d);else{if(b==="pre")x=q;else if(b==="br"&&x){a.add(new j.HtmlParser.Text("\n"));return}if(b==="br")A.push(d);else{var c=a.name,l=c&&(g[c]||(a._.isBlockLike?g.div:g.span));if(l&&!d.isUnknown&&!a.isUnknown&&!l[b]){l=v;var s;if(b in D&&
c in D){c=a.children;(c=c[c.length-1])&&c.name in G||t(c=new j.HtmlParser.Element("li"),a);C=a;s=c}else if(b==c)t(a,a.parent);else{t(a,a.parent,q);!F[c]&&c in g.$inline&&k.unshift(a);l=q}a=s?s:a.returnPoint||a.parent;if(l){p.onTagOpen.apply(this,arguments);return}}o(b);m();d.parent=a;d.returnPoint=C;C=0;if(d.isEmpty)t(d);else a=d}}};p.onTagClose=function(b){for(var f=k.length-1;f>=0;f--)if(b==k[f].name){k.splice(f,1);return}for(var h=[],d=[],c=a;c.type&&c.name!=b;){c._.isBlockLike||d.unshift(c);h.push(c);
c=c.parent}if(c.type){for(f=0;f<h.length;f++){var l=h[f];t(l,l.parent)}a=c;if(a.name=="pre")x=v;c._.isBlockLike&&m();t(c,c.parent);if(c==a)a=a.parent;k=k.concat(d)}if(b=="body")e=v};p.onText=function(b){if(!a._.hasInlineStarted&&!x){b=w.ltrim(b);if(b.length===0)return}m();o();if(e&&(!a.type||a.name=="body")&&w.trim(b))this.onTagOpen(e,{});x||(b=b.replace(/[\t\r\n ]{2,}|[\t\r\n]/g," "));a.add(new j.HtmlParser.Text(b))};p.onCDATA=function(b){a.add(new j.HtmlParser.cdata(b))};p.onComment=function(b){a.add(new j.HtmlParser.Comment(b))};
p.parse(i);for(m();a.type;){var u=a.parent,E=a;if(e&&(!u.type||u.name=="body")&&!g.$body[E.name]){a=u;p.onTagOpen(e,{});u=a}u.add(E);a=u}return B};r.augment(n,{add:function(i){var e=this.children.length;if(e=e>0&&this.children[e-1]||z){if(i._.isBlockLike&&e.type==y.NODE_TEXT){e.value=w.rtrim(e.value);if(e.value.length===0){this.children.pop();this.add(i);return}}e.next=i}i.previous=e;i.parent=this;this.children.push(i);this._.hasInlineStarted=i.type==y.NODE_TEXT||i.type==y.NODE_ELEMENT&&!i._.isBlockLike},
writeHtml:function(i,e){var o;this.filterChildren=this.filterChildren=function(){var m=new j.HtmlParser.BasicWriter;this.writeChildrenHtml.call(this,m,e,q);m=m.getHtml();this.children=(new n.FromHtml(m)).children;o=1};!this.name&&e&&e.onFragment(this);this.writeChildrenHtml(i,o?z:e)},writeChildrenHtml:function(i,e){for(var o=0;o<this.children.length;o++)this.children[o].writeHtml(i,e)}});j.HtmlParser.Fragment=n;j.HtmlParser.Fragment=n;n.FromHtml=n.FromHtml;r=n.prototype;j.Utils.extern(r,{add:r.add,
writeHtml:r.writeHtml,writeChildrenHtml:r.writeChildrenHtml})});
