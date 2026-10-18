import SwiftUI

struct QuizView: View {
    @Environment(StudyModel.self) private var model
    @State private var prompt: KanjiEntry?
    @State private var choices: [String] = []
    @State private var picked: String?
    @State private var correct = 0
    @State private var answered = 0

    var body: some View {
        NavigationStack {
            VStack(spacing: 20) {
                Text("\(correct) / \(answered)")
                    .font(.headline)
                    .foregroundStyle(Ink.muted)

                if let prompt {
                    Text(prompt.character)
                        .font(.system(size: 96))
                    Text(prompt.meaningEn)
                        .foregroundStyle(Ink.muted)

                    VStack(spacing: 10) {
                        ForEach(choices, id: \.self) { choice in
                            Button(choice) { choose(choice, prompt: prompt) }
                                .frame(maxWidth: .infinity)
                                .padding(.vertical, 12)
                                .background(color(for: choice, prompt: prompt), in: RoundedRectangle(cornerRadius: 12))
                                .foregroundStyle(Ink.text)
                                .disabled(picked != nil)
                        }
                    }

                    if picked != nil {
                        Button("နောက်တစ်ပုဒ်") { nextQuestion() }
                            .buttonStyle(.borderedProminent)
                            .tint(Ink.seal)
                    }
                }
                Spacer()
            }
            .padding(20)
            .frame(maxWidth: .infinity, maxHeight: .infinity)
            .background(Ink.paper)
            .navigationTitle("စမ်းမေးခွန်း")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .topBarTrailing) {
                    Button("ပြန်စ") { reset() }
                }
            }
            .onAppear {
                if prompt == nil { nextQuestion() }
            }
        }
    }

    private func color(for choice: String, prompt: KanjiEntry) -> Color {
        guard let picked else { return Ink.card }
        if choice == prompt.meaningMy { return Ink.moss.opacity(0.25) }
        if choice == picked { return Ink.seal.opacity(0.22) }
        return Ink.card
    }

    private func choose(_ choice: String, prompt: KanjiEntry) {
        guard picked == nil else { return }
        picked = choice
        answered += 1
        if choice == prompt.meaningMy { correct += 1 }
    }

    private func nextQuestion() {
        guard model.kanji.count > 3 else { return }
        let next = model.kanji.randomElement()!
        var options = Array(model.kanji.filter { $0.id != next.id }.shuffled().prefix(3).map(\.meaningMy))
        options.append(next.meaningMy)
        prompt = next
        choices = Array(Set(options)).shuffled()
        while choices.count < 4, let extra = model.kanji.map(\.meaningMy).first(where: { !choices.contains($0) }) {
            choices.append(extra)
        }
        picked = nil
    }

    private func reset() {
        correct = 0
        answered = 0
        picked = nil
        nextQuestion()
    }
}
