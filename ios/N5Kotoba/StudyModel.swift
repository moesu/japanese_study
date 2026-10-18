import Foundation
import Observation

struct Gloss: Codable, Hashable {
    let en: String
    let my: String
}

struct ExampleWord: Codable, Hashable, Identifiable {
    var id: String { word + "|" + reading }
    let word: String
    let reading: String
    let en: String
    let my: String
}

struct KanjiEntry: Codable, Identifiable, Hashable {
    var id: String { character }
    let character: String
    let strokes: Int
    let radical: String
    let onyomi: [String]
    let kunyomi: [String]
    let meaningEn: String
    let meaningMy: String
    let meanings: [Gloss]
    let examples: [ExampleWord]

    enum CodingKeys: String, CodingKey {
        case character, strokes, radical, onyomi, kunyomi, meanings, examples
        case meaningEn = "meaning_en"
        case meaningMy = "meaning_my"
    }

    var readingLine: String {
        let on = onyomi.isEmpty ? "—" : onyomi.joined(separator: "、")
        let kun = kunyomi.isEmpty ? "—" : kunyomi.joined(separator: "、")
        return "音 \(on)   訓 \(kun)"
    }
}

struct KotobaEntry: Codable, Identifiable, Hashable {
    var id: String { word + "|" + reading }
    let word: String
    let reading: String
    let en: String
    let my: String
    let kind: String
    let masu: String?

    var shown: String { masu ?? reading }
}

struct VocabGroup: Codable, Identifiable {
    var id: String { label }
    let label: String
    let words: [ExampleWord]
}

struct CharacterPiece: Identifiable {
    var id: String { "\(index)-\(char)" }
    let index: Int
    let char: String
    let isKanji: Bool
    let en: String?
    let my: String?
}

@MainActor
@Observable
final class StudyModel {
    let kanji: [KanjiEntry]
    let kotoba: [KotobaEntry]
    let personGroups: [VocabGroup]
    var learned: Set<String> {
        didSet { UserDefaults.standard.set(Array(learned), forKey: Self.learnedKey) }
    }

    private let kanjiByCharacter: [String: KanjiEntry]
    private static let learnedKey = "learned-kotoba"

    init() {
        kanji = Self.load("n5_kanji")
        kotoba = Self.load("n5_kotoba")
        personGroups = Self.load("n5_person_vocab")
        kanjiByCharacter = Dictionary(uniqueKeysWithValues: kanji.map { ($0.character, $0) })
        learned = Set(UserDefaults.standard.stringArray(forKey: Self.learnedKey) ?? [])
    }

    func isLearned(_ word: KotobaEntry) -> Bool {
        learned.contains(word.id)
    }

    func toggleLearned(_ word: KotobaEntry) {
        if learned.contains(word.id) {
            learned.remove(word.id)
        } else {
            learned.insert(word.id)
        }
    }

    func groups(for character: String) -> [VocabGroup] {
        character == "人" ? personGroups : []
    }

    func pieces(of word: String) -> [CharacterPiece] {
        word.enumerated().map { index, scalar in
            let char = String(scalar)
            if let known = kanjiByCharacter[char] {
                return CharacterPiece(index: index, char: char, isKanji: true, en: known.meaningEn, my: known.meaningMy)
            }
            if let extra = Self.extraMeanings[char] {
                return CharacterPiece(index: index, char: char, isKanji: true, en: extra.en, my: extra.my)
            }
            let han = scalar.unicodeScalars.contains { (0x4E00...0x9FFF).contains(Int($0.value)) }
            return CharacterPiece(index: index, char: char, isKanji: han, en: nil, my: nil)
        }
    }

    private static func load<T: Decodable>(_ name: String) -> T {
        let url = Bundle.main.url(forResource: name, withExtension: "json", subdirectory: "Resources")
            ?? Bundle.main.url(forResource: name, withExtension: "json")
        guard let url, let data = try? Data(contentsOf: url) else {
            fatalError("Missing \(name).json in the app bundle.")
        }
        do {
            return try JSONDecoder().decode(T.self, from: data)
        } catch {
            fatalError("Could not read \(name).json: \(error)")
        }
    }

    private static let extraMeanings: [String: Gloss] = [
        "不": Gloss(en: "not", my: "မဟုတ်"),
        "京": Gloss(en: "capital", my: "မြို့တော်"),
        "供": Gloss(en: "accompany", my: "အဖော်"),
        "元": Gloss(en: "origin", my: "မူလ"),
        "去": Gloss(en: "past", my: "အတိတ်"),
        "喫": Gloss(en: "consume", my: "သောက်စား"),
        "図": Gloss(en: "diagram", my: "ပုံ"),
        "地": Gloss(en: "ground", my: "မြေ"),
        "堂": Gloss(en: "hall", my: "ခန်းမ"),
        "屋": Gloss(en: "shop", my: "ဆိုင်"),
        "心": Gloss(en: "heart", my: "နှလုံး"),
        "意": Gloss(en: "mind", my: "စိတ်ကူး"),
        "曜": Gloss(en: "weekday", my: "နေ့နံ"),
        "有": Gloss(en: "have", my: "ရှိ"),
        "海": Gloss(en: "sea", my: "ပင်လယ်"),
        "港": Gloss(en: "port", my: "ဆိပ်ကမ်း"),
        "物": Gloss(en: "thing", my: "အရာ"),
        "的": Gloss(en: "target", my: "ပစ်မှတ်"),
        "神": Gloss(en: "god", my: "နတ်"),
        "科": Gloss(en: "department", my: "ဌာန"),
        "英": Gloss(en: "English", my: "အင်္ဂလိပ်"),
        "茶": Gloss(en: "tea", my: "လက်ဖက်"),
        "葉": Gloss(en: "leaf", my: "အရွက်"),
        "達": Gloss(en: "plural", my: "အများ"),
        "鉄": Gloss(en: "iron", my: "သံ"),
        "銀": Gloss(en: "silver", my: "ငွေသတ္တု"),
        "館": Gloss(en: "building", my: "အဆောက်အအုံ"),
        "鼻": Gloss(en: "nose", my: "နှာခေါင်း"),
    ]
}
