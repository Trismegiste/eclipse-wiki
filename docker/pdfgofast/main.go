package main

import (
	"context"
	"fmt"
	"io"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"time"

	"github.com/chromedp/cdproto/page"
	"github.com/chromedp/chromedp"
)

func indexHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Add("Content-Type", "text/html")
	http.ServeFile(w, r, "index.html")
}

const MAX_UPLOAD_SIZE = 50 * 1024 * 1024 // 50 MiB

func uploadHandler(w http.ResponseWriter, r *http.Request) {
	// mostly inspired by https://freshman.tech/file-upload-golang/

	// Is this POST ?
	if r.Method != "POST" {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// Checking max size of the uploaded file
	r.Body = http.MaxBytesReader(w, r.Body, MAX_UPLOAD_SIZE)
	file, fileHeader, err := r.FormFile("file")
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}
	defer file.Close()

	// Is the uploaded file html content ?
	if fileHeader.Header.Get("Content-Type") != "text/html" {
		http.Error(w, "Bad Mime Type", http.StatusUnprocessableEntity)
		return
	}

	// Preparing to dump the uploaded file with abstract filename
	pathname := fmt.Sprintf("/tmp/%d%s", time.Now().UnixNano(), filepath.Ext(fileHeader.Filename))
	dst, err := os.Create(pathname)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer dst.Close()

	// Copy the uploaded file to the filesystem
	_, err = io.Copy(dst, file)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	// Here we go, prints the html uploaded file stored on filesystem into a PDF response with the chromedriver calling chromium
	fmt.Printf("Converting html to PDF : %s\n", pathname)

	ctx, cancel := chromedp.NewContext(context.Background())
	defer cancel()

	var buf []byte
	if err := chromedp.Run(ctx,
		chromedp.Navigate("file://"+pathname),
		chromedp.ActionFunc(func(ctx context.Context) error {
			var err error
			buf, _, err = page.PrintToPDF().
				WithDisplayHeaderFooter(false).
				// https://pkg.go.dev/github.com/chromedp/cdproto@v0.0.0-20240709201219-e202069cc16b/page#PrintToPDFParams.WithPrintBackground
				WithPrintBackground(true).
				WithMarginBottom(0).
				WithMarginTop(0).
				WithMarginLeft(0).
				WithMarginRight(0).
				WithPreferCSSPageSize(true).
				Do(ctx)
			return err
		}),
	); err != nil {
		log.Fatal(err)
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}

	w.Write(buf)
}

func main() {
	// Routing config
	mux := http.NewServeMux()
	mux.HandleFunc("/", indexHandler)
	mux.HandleFunc("/upload", uploadHandler)

	// launching server
	var port string = os.Args[1]
	if err := http.ListenAndServe(":"+port, mux); err != nil {
		log.Fatal(err)
	}
}
