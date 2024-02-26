import { deleteVertex } from './business';

describe('Quick NPC creation', () => {

    let randomness = null
    let fixture = {}
    let creationGraph = null

    beforeEach(() => {
        cy.fixture('npcgraph').then(c => {
            fixture = c
            if (randomness === null) {
                const array = new Uint32Array(1)
                crypto.getRandomValues(array)
                randomness = array[0]
            }
            fixture.title += ' ' + randomness
        })

        cy.readFile('./var/storage/dev/quick-creation.graph').then(content => {
            creationGraph = {
                tree: Object.values(JSON.parse(content)),
                search(key) {
                    const found = this.tree.find(node => {
                        return node.name === key
                    })
                    if (typeof found === 'undefined') {
                        throw new Error('The node named "' + key + '" is unknown')
                    } else {
                        return found
                    }
                },
                getFirstChild(key) {
                    const found = this.search(key)
                    return found.children.length ? found.children[0] : null
                },
                getFirstDescendantsFrom(ancestors = ['root']) {
                    const father = ancestors.at(-1)
                    const firstChild = this.getFirstChild(father)
                    if (firstChild !== null) {
                        ancestors.push(firstChild)
                        return this.getFirstDescendantsFrom(ancestors)
                    } else {
                        return ancestors
                }
                }
            }
        })
    })

    it('lauches a quick npc creation', () => {
        cy.visit('/')
        cy.get('main .icon-npcgraph').click()
        cy.get('.node-selection article h2').contains('root')
        const path = creationGraph.getFirstDescendantsFrom(['homme'])
        cy.get('.node-selection footer label').contains(path[0]).click()
        cy.get('.node-selection footer label').contains(path[1]).click()
        cy.get('.node-selection footer label').contains(path[2]).click()
        cy.get('.node-selection footer label').contains(path[3]).click()
        cy.get('.node-selection footer label').contains(path[4]).click()
        cy.get('main form a').contains('Random name').click()
        cy.get('#selector_title').should('not.have.value', '')
        cy.get('#selector_title').type(`{selectAll}${fixture.title}`)

        cy.get('.result-selection h2').contains('Background').parents('div').first().find('ul > li > input[type=radio]').first().click()
        cy.get('.result-selection h2').contains('Faction').parents('div').first().find('ul > li > input[type=radio]').first().click()
        cy.get('.result-selection h2').contains('Morphe').parents('div').first().find('ul > li > input[type=radio]').first().click()

        cy.intercept('/invokeai/ajax/local/*').as('getAvatar')
        cy.get('.result-selection h2').contains('Keywords').parents('div').first()
                .find('ul > li > input[type=checkbox]')
                .not('#male-1')
                .click({multiple: true})
        cy.wait('@getAvatar')
        cy.get('.avatar-suggest img').first().click()

        cy.get('#selector_generate').click()
    })

    it('shows the quick npc', () => {
        cy.visit('/wiki/' + fixture.title)

        // profile pic
        cy.get('img[src^="/picture/get/token"').should('exist')
        cy.get('img[src^="/profile/unique/"]').click()
        cy.get('.notif .flash-success').contains('sent')

        // visual
        cy.get('.parsed-wikitext a[href^="/picture/push"]').click()
        cy.get('section.notif div.flash-success').should('have.length', 2)
        cy.screenshot('Quick NPC show', {overwrite: true})
    })

    it('deletes the NPC', () => {
        deleteVertex(fixture.title)
    })

})