# imgf
Show photos from tumblr. blogs with pagination

Demo: [https://imgf.berke.li](https://imgf.berke.li)

# Nginx rewrite

```
location / {
	rewrite "^/page/([0-9]{1,4})/?$" /index.php?page=$1 last;

    rewrite "^/@(\w{2,30})/?$" /index.php?blog=$1 last;
    rewrite "^/@(\w{2,30})/page/([0-9]{1,4})/?$" /index.php?blog=$1&page=$2 last;
}
```
