const reactivePortfolio = reactive(loadReactivePortfolio());

function loadReactivePortfolio() {
    let portfolio = [portfolioFactory('My Portfolio')];

    if (localStorage.portfoliov1) {
        portfolio = JSON.parse(LZUTF8.decompress(localStorage.portfoliov1, {"inputEncoding":"StorageBinaryString"}));
    }

    portfolio.forEach(p => {
        if (p.history === undefined) {
            p.sell_history = [];
        }
    });

    return portfolio;
}

function portfolioFactory(name) {
    return {
        'name': name,
        'uid': 'portfolio-' + uid(),
        'date_created': Date.now(),
        'positions': [],
    }
}

function savePortfolio() {
    localStorage.portfoliov1 = LZUTF8.compress(JSON.stringify(reactivePortfolio), {"outputEncoding":"StorageBinaryString"});
    const event = new CustomEvent('portfolioSavedNonEsm', {detail: reactivePortfolio});
    document.dispatchEvent(event);
}

function addPortfolio(name) {
    reactivePortfolio.push(portfolioFactory(name));
}

function removePortfolio(portfolioId) {
    const index = reactivePortfolio.findIndex(portfolio => portfolio.uid === portfolioId);
    reactivePortfolio.splice(index, 1);
}

/**
 * Helper function to create a portfolio position object in the correct format. Returns an object in the following format:
 *  {
 *      uid: <str>
 *      date_modified: <Unix millis>
 *      item_id: <int>
 *      qty: <int>
 *      mark: <float>
 *      mark_type: enum "gold", "sb"
 *      maturity_date: <Unix millis> or null
 *      maturity_price: <float> or null
 *  }
 * @param {int} item_id
 * @param {int} qty
 * @param {float} mark
 * @param {string} mark_type Must be "gold" (default) or "sb"
 * @param {int|null} maturity_date Must be a millisecond Unix timestamp or null
 * @param {float|null} maturity_price Must be float or null
 * @return {Object}
 */
function positionFactory(item_id, qty, mark, mark_type = "gold", maturity_date = null, maturity_price = null) {
    if (mark_type !== 'gold' && mark_type !== 'sb') {
        throw 'Invalid portfolio benchmark type.';
    }

    return {
        'uid': "position-" + uid(),
        'date_modified': Date.now(),
        'item_id': Number(item_id),
        'qty': Number(qty),
        'mark': Number(mark),
        'mark_type': mark_type,
        'maturity_date': (maturity_date == null) ? null : Number(maturity_date),
        'maturity_price': (maturity_price == null) ? null : Number(maturity_price),
    };
}

function addPosition(portfolioId, positionObject) {
    reactivePortfolio.find(portfolio => portfolio.uid === portfolioId).positions.push(positionObject);
}

function removePosition(portfolioId, positionId) {
    const portfolio = reactivePortfolio.find(portfolio => portfolio.uid === portfolioId);
    const positionIndex = portfolio.positions.findIndex(position => position.uid === positionId);
    portfolio.positions.splice(positionIndex, 1);
}

function editPosition(portfolioId, positionId, qty, mark, maturity_date = null, maturity_price = null) {
    const portfolio = reactivePortfolio.find(portfolio => portfolio.uid === portfolioId);
    const position = portfolio.positions.find(position => position.uid === positionId);

    position.qty = qty;
    position.mark = mark;
    position.maturity_date = maturity_date;
    position.maturity_price = maturity_price;
}

function movePosition(portfolioId, positionId, newPortfolioId) {
    if (portfolioId === newPortfolioId) {
        return;
    }

    const oldPortfolio = reactivePortfolio.find(portfolio => portfolio.uid === portfolioId);
    const newPortfolio = reactivePortfolio.find(portfolio => portfolio.uid === newPortfolioId);
    const positionIndex = oldPortfolio.positions.findIndex(position => position.uid === positionId);

    const position = oldPortfolio.positions.splice(positionIndex, 1)[0];
    newPortfolio.positions.push(position);
}

/**
 *
 * @param {string} portfolioId
 * @param {int} itemId
 * @param {int} qty
 * @param {int|float} avgCost
 * @param {int|float} sellPrice
 * @param {String} markType
 */
function appendSellHistory(portfolioId, itemId, qty, avgCost, sellPrice, markType) {
    if (markType !== 'gold' && markType !== 'sb') {
        throw 'Invalid mark type.';
    }

    const portfolio = reactivePortfolio.find(portfolio => portfolio.uid === portfolioId);

    portfolio.sell_history.push({
        uid: "hist-" + uid(),
        date: Date.now(),
        item_id: Number(itemId),
        qty: Number(qty),
        avg_cost: Number(avgCost),
        sell_price: Number(sellPrice),
        mark_type: markType
    })
}

watch(reactivePortfolio, (oldVal, newVal) => {
    savePortfolio();
});