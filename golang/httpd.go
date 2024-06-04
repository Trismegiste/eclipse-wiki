package main

import (
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"os"
)

func computeFloydWarshall(w http.ResponseWriter, r *http.Request) {
	fmt.Printf("Floyd-Warshall request\n")
	body, err := io.ReadAll(r.Body)
	if err != nil {
		fmt.Printf("could not read body: %s\n", err)
	}

	var matrix [][]int
	json.Unmarshal(body, &matrix)
	dim := len(matrix)

	for k := 0; k < dim; k++ {
		for line := 0; line < dim; line++ {
			for column := 0; column < dim; column++ {
				newSum := matrix[line][k] + matrix[k][column]
				if newSum < matrix[line][column] {
					matrix[line][column] = newSum
				}
			}
		}
	}

	enc := json.NewEncoder(w)
	enc.Encode(matrix)
}

func main() {
	http.HandleFunc("/algebra/floydwarshall", computeFloydWarshall)

	err := http.ListenAndServe(":3333", nil)
	if errors.Is(err, http.ErrServerClosed) {
		fmt.Printf("server closed\n")
	} else if err != nil {
		fmt.Printf("error starting server: %s\n", err)
		os.Exit(1)
	}
}
