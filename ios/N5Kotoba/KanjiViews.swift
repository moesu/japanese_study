import SwiftUI

struct KanjiListView: View {
    @Environment(StudyModel.self) private var model
    @State private var query = ""

    private var results: [KanjiEntry] {
        let needle = query.trimmingCharacters(in: .whitespacesAndNewlines).lowercased()
        guard !needle.isEmpty else { return model.kanji }
        return model.kanji.filter { kanji in
            let haystack = (
                kanji.character
                    + kanji.meaningEn
                    + kanji.meaningMy
                    + kanji.onyomi.joined()
                    + kanji.kunyomi.joined()
                    + kanji.meanings.map { $0.en + $0.my }.joined()
                    + kanji.examples.map { $0.word + $0.reading + $0.en + $0.my }.joined()
            ).lowercased()
            return haystack.contains(needle)
        }
    }

    var body: some View {
        NavigationStack {
            ScrollView {
                LazyVGrid(columns: [GridItem(.adaptive(minimum: 104), spacing: 10)], spacing: 10) {
                    ForEach(results) { kanji in
                        NavigationLink(value: kanji) {
                            VStack(spacing: 4) {
                                Text(kanji.character)
                                    .font(.system(size: 36))
                                    .foregroundStyle(Ink.text)
                                Text(kanji.meaningMy)
                                    .font(.subheadline)
                                    .foregroundStyle(Ink.seal)
                                    .lineLimit(1)
                            }
                            .frame(maxWidth: .infinity, minHeight: 96)
                            .background(Ink.card, in: RoundedRectangle(cornerRadius: 14))
                        }
                        .buttonStyle(.plain)
                    }
                }
                .padding(16)
            }
            .background(Ink.paper)
            .navigationTitle("N5 漢字")
            .navigationBarTitleDisplayMode(.inline)
            .navigationDestination(for: KanjiEntry.self) { kanji in
                KanjiDetailView(kanji: kanji)
            }
            .searchable(text: $query, prompt: "漢字၊ နေ့၊ day")
            .toolbar {
                ToolbarItem(placement: .principal) {
                    VStack(spacing: 0) {
                        Text("N5 漢字").font(.headline)
                        Text("\(results.count) / \(model.kanji.count)")
                            .font(.caption)
                            .foregroundStyle(Ink.muted)
                    }
                }
            }
        }
    }
}

struct KanjiDetailView: View {
    @Environment(StudyModel.self) private var model
    let kanji: KanjiEntry

    private var index: Int {
        model.kanji.firstIndex(of: kanji) ?? 0
    }

    var body: some View {
        ScrollView {
            VStack(alignment: .leading, spacing: 18) {
                VStack(spacing: 6) {
                    Text("\(kanji.strokes) strokes · \(kanji.radical)")
                        .font(.caption)
                        .foregroundStyle(Ink.muted)
                    Text(kanji.character)
                        .font(.system(size: 88))
                    Text(kanji.meaningMy)
                        .font(.title2)
                        .foregroundStyle(Ink.seal)
                    Text(kanji.meaningEn)
                        .foregroundStyle(Ink.muted)
                    Text(kanji.readingLine)
                        .font(.subheadline)
                        .multilineTextAlignment(.center)
                }
                .frame(maxWidth: .infinity)
                .padding(20)
                .background(Ink.card, in: RoundedRectangle(cornerRadius: 18))

                sectionTitle("အဓိပ္ပာယ်များ")
                VStack(spacing: 0) {
                    ForEach(kanji.meanings, id: \.self) { meaning in
                        HStack {
                            Text(meaning.my)
                            Spacer()
                            Text(meaning.en).foregroundStyle(Ink.muted)
                        }
                        .padding(.vertical, 8)
                        Divider()
                    }
                }
                .padding(.horizontal, 14)
                .background(Ink.card, in: RoundedRectangle(cornerRadius: 14))

                let groups = model.groups(for: kanji.character)
                if groups.isEmpty {
                    sectionTitle("ဥပမာစကားလုံး")
                    ForEach(kanji.examples) { example in
                        ExampleCard(example: example)
                    }
                } else {
                    sectionTitle("N5 ဝေါဟာရ · လူ")
                    ForEach(groups) { group in
                        Text(group.label)
                            .font(.headline)
                        ForEach(group.words) { example in
                            ExampleCard(example: example)
                        }
                    }
                }
            }
            .padding(16)
        }
        .background(Ink.paper)
        .navigationTitle(kanji.character)
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .bottomBar) {
                HStack {
                    if index > 0 {
                        NavigationLink(value: model.kanji[index - 1]) {
                            Text("← \(model.kanji[index - 1].character)")
                        }
                    }
                    Spacer()
                    if index + 1 < model.kanji.count {
                        NavigationLink(value: model.kanji[index + 1]) {
                            Text("\(model.kanji[index + 1].character) →")
                        }
                    }
                }
            }
        }
    }

    private func sectionTitle(_ title: String) -> some View {
        Text(title).font(.headline)
    }
}

struct ExampleCard: View {
    @Environment(StudyModel.self) private var model
    let example: ExampleWord

    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            HStack(alignment: .top, spacing: 8) {
                ForEach(model.pieces(of: example.word)) { piece in
                    VStack(spacing: 2) {
                        Text(piece.char)
                            .font(.title3)
                        if let my = piece.my {
                            Text(my)
                                .font(.caption2)
                                .foregroundStyle(Ink.seal)
                                .multilineTextAlignment(.center)
                            if let en = piece.en {
                                Text(en)
                                    .font(.caption2)
                                    .foregroundStyle(Ink.muted)
                            }
                        }
                    }
                    .frame(minWidth: 36)
                }
            }
            Text(example.reading)
                .font(.subheadline)
                .foregroundStyle(Ink.muted)
            Text(example.my)
                + Text("  ·  \(example.en)").foregroundColor(Ink.muted)
        }
        .frame(maxWidth: .infinity, alignment: .leading)
        .padding(12)
        .background(Ink.card, in: RoundedRectangle(cornerRadius: 14))
    }
}
