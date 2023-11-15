// dice roler library

let diceCount = 100
let container = [] // FIFO

function rollOne(side) {
    if (container.length === 0) {
        return new Promise(function (fulfill, reject) {
            fetch('https://www.random.org/integers/?num='
                    + diceCount
                    + '&min=1&max=120&col='
                    + diceCount
                    + '&base=10&format=plain&rnd=new').then(function (response) {
                return response.text()
            }).then(function (content) {
                var extracted = content.split("\t");
                for (var k = 0; k < diceCount; k++) {
                    container[k] = parseInt(extracted[k]);
                }

                rollOne(side).then(function (r) {
                    fulfill(r)
                })
            })
        })
    } else {
        return new Promise(function (fulfill, reject) {
            let r = container.shift()
            fulfill(1 + r % side)
        })

    }
}

function recursiveRollOne(side) {
    return new Promise(function (fulfill, reject) {
        rollOne(side).then(function (first) {
            if (first < side) {
                fulfill(first)
            } else {
                recursiveRollOne(side).then(function (reroll) {
                    fulfill(first + reroll)
                })
            }
        })
    })
}

export default function rollPool(pool) {  // array of sides
    return new Promise(function (fulfill, reject) {
        if (pool.length === 0) {
            fulfill([])
        } else {
            let head = pool.shift()
            recursiveRollOne(head).then(function (first) {
                rollPool(pool).then(function (result) {
                    result.unshift(first)
                    fulfill(result)
                })
            })
        }
    })
}
